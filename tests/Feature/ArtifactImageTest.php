<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtifactImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_replace_remove_and_customer_can_view_artifact_image(): void
    {
        Storage::fake('public');
        [$admin, $customer] = $this->accounts();
        $category = Category::create(['name' => 'Image Category']);
        $unit = $this->unit();
        $first = UploadedFile::fake()->image('first.jpg', 20, 20);

        $this->actingAs($admin)->post(route('admin.artifacts.store'), $this->payload($unit, $category, $first))->assertRedirect();
        $unit = Unit::firstOrFail();
        $oldPath = $unit->image;
        $this->assertStringStartsWith('artifacts/', $oldPath);
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($customer)->get(route('catalog.show', $unit))->assertOk()->assertSee(Storage::disk('public')->url($oldPath), false);

        $second = UploadedFile::fake()->image('second.png', 20, 20);
        $this->actingAs($admin)->put(route('admin.artifacts.update', $unit), $this->payload($unit->toArray(), $category, $second))->assertRedirect();
        $newPath = $unit->fresh()->image;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        $this->actingAs($admin)->put(route('admin.artifacts.update', $unit), $this->payload($unit->fresh()->toArray(), $category) + ['remove_image' => '1'])->assertRedirect();
        $this->assertNull($unit->fresh()->image);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_image_is_optional_and_existing_image_is_kept_without_replacement(): void
    {
        Storage::fake('public');
        [$admin] = $this->accounts();
        $category = Category::create(['name' => 'Optional Image']);
        $unit = $this->unit();
        $this->actingAs($admin)->post(route('admin.artifacts.store'), $this->payload($unit, $category))->assertRedirect();
        $unit = Unit::firstOrFail();
        $this->assertNull($unit->image);

        $image = UploadedFile::fake()->image('kept.webp', 20, 20);
        $this->actingAs($admin)->put(route('admin.artifacts.update', $unit), $this->payload($unit->toArray(), $category, $image))->assertRedirect();
        $path = $unit->fresh()->image;
        $this->actingAs($admin)->put(route('admin.artifacts.update', $unit), $this->payload($unit->fresh()->toArray(), $category))->assertRedirect();
        $this->assertSame($path, $unit->fresh()->image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_invalid_or_oversized_images_are_rejected_and_customer_cannot_upload(): void
    {
        Storage::fake('public');
        [$admin, $customer] = $this->accounts();
        $category = Category::create(['name' => 'Validation Image']);
        $payload = $this->payload($this->unit(), $category);

        $this->actingAs($admin)->post(route('admin.artifacts.store'), $payload + ['image' => UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream')])->assertSessionHasErrors('image');
        $this->assertDatabaseCount('units', 0);
        $this->actingAs($admin)->post(route('admin.artifacts.store'), $payload + ['image' => UploadedFile::fake()->create('large.jpg', 2049, 'image/jpeg')])->assertSessionHasErrors('image');
        $this->actingAs($customer)->post(route('admin.artifacts.store'), $payload + ['image' => UploadedFile::fake()->image('customer.jpg')])->assertForbidden();
    }

    private function accounts(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        Profile::create(['user_id' => $admin->id]);
        Profile::create(['user_id' => $customer->id]);
        return [$admin, $customer];
    }

    private function unit(): array
    {
        return ['name' => 'Image Artifact', 'unit_code' => 'IMG-001', 'description' => 'Image test artifact', 'magical_effect' => 'Glow', 'rental_price' => 10000, 'rarity' => 'Rare', 'condition' => 'GOOD'];
    }

    private function payload(array $unit, Category $category, ?UploadedFile $image = null): array
    {
        $payload = array_merge($this->unit(), ['unit_code' => $unit['unit_code'] ?? 'IMG-001', 'category_ids' => [$category->id]]);
        if ($image) $payload['image'] = $image;
        return $payload;
    }
}
