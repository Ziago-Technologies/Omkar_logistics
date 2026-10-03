<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find existing invoice with GSTOML2627004 (series '26-27', invoice_no 4)
        $invoice4 = DB::table('invoices')
            ->where('series', '26-27')
            ->where('invoice_no', 4)
            ->first();

        if ($invoice4) {
            // If another invoice already has invoice_no 45 (e.g. from local test submissions),
            // safely clean it up to prevent unique constraint violation on (series, invoice_no)
            $conflict45 = DB::table('invoices')
                ->where('series', '26-27')
                ->where('invoice_no', 45)
                ->where('id', '!=', $invoice4->id)
                ->first();

            if ($conflict45) {
                DB::table('bilties')->where('invoice_id', $conflict45->id)->update(['invoice_id' => null]);
                DB::table('invoice_items')->where('invoice_id', $conflict45->id)->delete();
                DB::table('invoices')->where('id', $conflict45->id)->delete();
            }

            DB::table('invoices')
                ->where('id', $invoice4->id)
                ->update(['invoice_no' => 45]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('invoices')
            ->where('series', '26-27')
            ->where('invoice_no', 45)
            ->update(['invoice_no' => 4]);
    }
};
