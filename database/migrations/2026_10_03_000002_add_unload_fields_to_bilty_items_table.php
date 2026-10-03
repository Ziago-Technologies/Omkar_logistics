<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('bilty_items')) {
            Schema::table('bilty_items', function (Blueprint $table) {
                if (!Schema::hasColumn('bilty_items', 'unload_rate')) {
                    $table->decimal('unload_rate', 12, 2)->default(0.00)->after('rate');
                }
                if (!Schema::hasColumn('bilty_items', 'unload_amount')) {
                    $table->decimal('unload_amount', 12, 2)->default(0.00)->after('unload_rate');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bilty_items')) {
            Schema::table('bilty_items', function (Blueprint $table) {
                if (Schema::hasColumn('bilty_items', 'unload_rate')) {
                    $table->dropColumn('unload_rate');
                }
                if (Schema::hasColumn('bilty_items', 'unload_amount')) {
                    $table->dropColumn('unload_amount');
                }
            });
        }
    }
};
