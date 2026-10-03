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
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'unit_filter')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('unit_filter')->nullable()->after('destination_filter');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'unit_filter')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('unit_filter');
            });
        }
    }
};
