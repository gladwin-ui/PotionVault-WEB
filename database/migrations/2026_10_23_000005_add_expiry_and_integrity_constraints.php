<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->nullOnDelete();
            $table->text('refund_notes')->nullable()->after('refunded_by');
        });
        Schema::table('rental_items', function (Blueprint $table) {
            $table->unique(['rental_id', 'unit_id'], 'rental_items_rental_unit_unique');
            $table->index(['unit_id', 'returned_at'], 'rental_items_unit_returned_index');
        });
        Schema::table('rentals', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'rentals_user_status_index');
            $table->index(['status', 'expires_at'], 'rentals_status_expires_index');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) { $table->dropIndex('rentals_user_status_index'); $table->dropIndex('rentals_status_expires_index'); });
        Schema::table('rental_items', function (Blueprint $table) { $table->dropIndex('rental_items_unit_returned_index'); $table->dropUnique('rental_items_rental_unit_unique'); });
        Schema::table('payments', function (Blueprint $table) { $table->dropForeign(['refunded_by']); $table->dropColumn(['refunded_at', 'refunded_by', 'refund_notes']); });
    }
};
