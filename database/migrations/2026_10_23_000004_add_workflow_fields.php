<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rentals', fn (Blueprint $table) => $table->text('rejection_reason')->nullable()->after('expires_at'));
        Schema::table('rental_items', function (Blueprint $table) { $table->text('return_notes')->nullable()->after('return_condition'); $table->timestamp('fine_paid_at')->nullable()->after('fine_payment_status'); $table->foreignId('fine_confirmed_by')->nullable()->after('fine_paid_at')->constrained('users')->nullOnDelete(); });
    }
    public function down(): void
    {
        Schema::table('rental_items', function (Blueprint $table) { $table->dropForeign(['fine_confirmed_by']); $table->dropColumn(['return_notes', 'fine_paid_at', 'fine_confirmed_by']); }); Schema::table('rentals', fn (Blueprint $table) => $table->dropColumn('rejection_reason'));
    }
};
