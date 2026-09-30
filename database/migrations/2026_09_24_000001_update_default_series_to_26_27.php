<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // 1. Re-sequence payments under series 26-27 chronologically to prevent unique constraint collisions
            if (Schema::hasTable('payments')) {
                $payments = DB::table('payments')->orderBy('payment_date')->orderBy('id')->get();
                
                // Temporary high offset to avoid collisions
                foreach ($payments as $idx => $p) {
                    $tempNo = 90000 + $idx + 1;
                    DB::table('payments')->where('id', $p->id)->update([
                        'payment_no' => $tempNo,
                        'voucher_no' => (string)$tempNo,
                    ]);
                }
                
                // Final sequential numbers under 26-27
                foreach ($payments as $idx => $p) {
                    $seqNo = $idx + 1;
                    DB::table('payments')->where('id', $p->id)->update([
                        'series'     => '26-27',
                        'payment_no' => $seqNo,
                        'voucher_no' => (string)$seqNo,
                    ]);
                }
            }

            // 2. Update all tables where series = 'A' or 'a' to '26-27'
            $tables = ['bilties', 'invoices', 'receipts', 'receipt_items'];
            foreach ($tables as $tbl) {
                if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'series')) {
                    DB::table($tbl)
                        ->where('series', 'A')
                        ->orWhere('series', 'a')
                        ->update(['series' => '26-27']);
                }
            }

            // 3. Remove series 'A' from the series master table and ensure '26-27'
            if (Schema::hasTable('series')) {
                $series2627 = DB::table('series')->where('name', '26-27')->first();
                if (!$series2627) {
                    $series2627Id = DB::table('series')->insertGetId([
                        'name'        => '26-27',
                        'description' => 'FY 2026-2027',
                        'is_active'   => 1,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                } else {
                    $series2627Id = $series2627->id;
                }

                $seriesAIds = DB::table('series')->where('name', 'A')->orWhere('name', 'a')->pluck('id')->toArray();
                if (!empty($seriesAIds)) {
                    if (Schema::hasTable('bilties') && Schema::hasColumn('bilties', 'series_id')) {
                        DB::table('bilties')->whereIn('series_id', $seriesAIds)->update(['series_id' => $series2627Id]);
                    }
                    DB::table('series')->whereIn('id', $seriesAIds)->delete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op or revert if necessary
    }
};
