<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create(['name' => 'PotionVault Admin', 'email' => 'admin@potionvault.test', 'role' => 'admin']);
        $user = User::factory()->create(['name' => 'Aria Willowbrook', 'email' => 'aria@potionvault.test', 'role' => 'user']);
        Profile::create(['user_id' => $admin->id]); Profile::create(['user_id' => $user->id, 'phone' => '+62 812 4455 7711', 'address' => 'The Lantern District, Jakarta']);
        foreach ([['Healing', 'Restorative vessels'], ['Illusion', 'Light-bending artifacts'], ['Mana', 'Arcane energy vessels'], ['Elemental', 'Primal energy containers'], ['Temporal', 'Time-bending vessels']] as [$name, $description]) Category::create(compact('name', 'description'));
        foreach ([['HEAL-001', 'Healing Potion Vessel', 'Common', 25000, 'Healing'], ['INVS-001', 'Invisibility Flask', 'Rare', 40000, 'Illusion'], ['MANA-001', 'Mana Crystal Bottle', 'Rare', 35000, 'Mana'], ['TIME-001', 'Time Freeze Vessel', 'Epic', 60000, 'Temporal'], ['PHNX-001', 'Phoenix Flame Bottle', 'Legendary', 100000, 'Elemental']] as [$code, $name, $rarity, $price, $category]) { $unit = Unit::create(['unit_code' => $code, 'name' => $name, 'description' => "A carefully maintained {$name} for responsible magical rental.", 'magical_effect' => 'Fictional magical effect for the PotionVault catalog.', 'rarity' => $rarity, 'rental_price' => $price]); $unit->categories()->sync([Category::where('name', $category)->value('id')]); }
    }
}
