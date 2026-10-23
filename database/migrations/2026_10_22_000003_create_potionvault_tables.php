<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) { $table->string('role')->default('user')->after('password'); $table->boolean('is_active')->default(true)->after('role'); });
        Schema::create('profiles', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->string('phone')->nullable(); $table->text('address')->nullable(); $table->timestamps(); });
        Schema::create('units', function (Blueprint $table) { $table->id(); $table->string('unit_code')->unique(); $table->string('name'); $table->text('description'); $table->text('magical_effect')->nullable(); $table->decimal('rental_price', 12, 2); $table->string('rarity'); $table->string('condition')->default('GOOD'); $table->string('status')->default('AVAILABLE'); $table->string('image')->nullable(); $table->softDeletes(); $table->timestamps(); });
        Schema::create('categories', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->text('description')->nullable(); $table->timestamps(); });
        Schema::create('category_unit', function (Blueprint $table) { $table->foreignId('unit_id')->constrained()->cascadeOnDelete(); $table->foreignId('category_id')->constrained()->cascadeOnDelete(); $table->primary(['unit_id', 'category_id']); });
        Schema::create('rentals', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained(); $table->string('status')->default('PENDING'); $table->decimal('rental_total', 12, 2)->default(0); $table->timestamp('approved_at')->nullable(); $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('expires_at')->nullable(); $table->timestamps(); });
        Schema::create('rental_items', function (Blueprint $table) { $table->id(); $table->foreignId('rental_id')->constrained()->cascadeOnDelete(); $table->foreignId('unit_id')->constrained(); $table->decimal('rental_price_snapshot', 12, 2); $table->timestamp('borrowed_at')->nullable(); $table->timestamp('due_at')->nullable(); $table->timestamp('returned_at')->nullable(); $table->string('return_condition')->nullable(); $table->decimal('fine_amount', 12, 2)->default(0); $table->string('fine_payment_status')->default('NO_FINE'); $table->timestamps(); });
        Schema::create('payments', function (Blueprint $table) { $table->id(); $table->foreignId('rental_id')->unique()->constrained()->cascadeOnDelete(); $table->decimal('amount', 12, 2); $table->string('status')->default('UNPAID'); $table->timestamp('paid_at')->nullable(); $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('payments'); Schema::dropIfExists('rental_items'); Schema::dropIfExists('rentals'); Schema::dropIfExists('category_unit'); Schema::dropIfExists('categories'); Schema::dropIfExists('units'); Schema::dropIfExists('profiles'); Schema::table('users', fn (Blueprint $table) => [$table->dropColumn(['role', 'is_active'])]); }
};
