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
        // 1. Add city_id to locations table if it doesn't exist
        if (Schema::hasTable('locations') && !Schema::hasColumn('locations', 'city_id')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->unsignedBigInteger('city_id')->nullable()->after('company_id');
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            });
        }

        // 2. Add explicit geographic columns to bilties table
        if (Schema::hasTable('bilties')) {
            Schema::table('bilties', function (Blueprint $table) {
                if (!Schema::hasColumn('bilties', 'from_city_id')) {
                    $table->unsignedBigInteger('from_city_id')->nullable()->after('invoice_date');
                    $table->foreign('from_city_id')->references('id')->on('cities')->onDelete('set null');
                }
                if (!Schema::hasColumn('bilties', 'to_city_id')) {
                    $table->unsignedBigInteger('to_city_id')->nullable()->after('from_city_id');
                    $table->foreign('to_city_id')->references('id')->on('cities')->onDelete('set null');
                }
                if (!Schema::hasColumn('bilties', 'from_state_id')) {
                    $table->unsignedBigInteger('from_state_id')->nullable()->after('to_city_id');
                    $table->foreign('from_state_id')->references('id')->on('states')->onDelete('set null');
                }
                if (!Schema::hasColumn('bilties', 'to_state_id')) {
                    $table->unsignedBigInteger('to_state_id')->nullable()->after('from_state_id');
                    $table->foreign('to_state_id')->references('id')->on('states')->onDelete('set null');
                }
                if (!Schema::hasColumn('bilties', 'from_country_id')) {
                    $table->unsignedBigInteger('from_country_id')->nullable()->after('to_state_id');
                    $table->foreign('from_country_id')->references('id')->on('countries')->onDelete('set null');
                }
                if (!Schema::hasColumn('bilties', 'to_country_id')) {
                    $table->unsignedBigInteger('to_country_id')->nullable()->after('from_country_id');
                    $table->foreign('to_country_id')->references('id')->on('countries')->onDelete('set null');
                }
            });
        }

        // 3. Backfill existing Location and Bilty data with zero data loss
        $this->backfillGeographicHierarchy();
    }

    /**
     * Backfill locations & bilties data using fuzzy/exact matching with cities, states, and countries
     */
    private function backfillGeographicHierarchy(): void
    {
        // A. Link locations to cities
        if (Schema::hasTable('locations') && Schema::hasTable('cities')) {
            $locations = DB::table('locations')->get();
            foreach ($locations as $loc) {
                if (empty($loc->name)) continue;
                $cityName = trim($loc->name);
                
                // Match with city
                $city = DB::table('cities')->where('name', 'like', $cityName)->first();
                if (!$city) {
                    $city = DB::table('cities')->where('name', 'like', '%' . $cityName . '%')->first();
                }
                
                if ($city) {
                    DB::table('locations')->where('id', $loc->id)->update([
                        'city_id' => $city->id
                    ]);
                }
            }
        }

        // B. Backfill Bilties with from_city_id, to_city_id, state_id, country_id
        if (Schema::hasTable('bilties')) {
            $bilties = DB::table('bilties')->get();
            $defaultCountryId = DB::table('countries')->value('id') ?: 1;

            foreach ($bilties as $bilty) {
                $fromCityId = null;
                $fromStateId = null;
                $fromCountryId = $defaultCountryId;

                $toCityId = null;
                $toStateId = null;
                $toCountryId = $defaultCountryId;

                // Process From Location
                if ($bilty->from_location_id) {
                    $loc = DB::table('locations')->where('id', $bilty->from_location_id)->first();
                    if ($loc && !empty($loc->city_id)) {
                        $fromCityId = $loc->city_id;
                    } else {
                        // Check if from_location_id was directly referencing a city id
                        $city = DB::table('cities')->where('id', $bilty->from_location_id)->first();
                        if ($city) {
                            $fromCityId = $city->id;
                        } elseif ($loc && !empty($loc->name)) {
                            $cityByName = DB::table('cities')->where('name', 'like', '%' . trim($loc->name) . '%')->first();
                            if ($cityByName) $fromCityId = $cityByName->id;
                        }
                    }
                }

                // Process To Location
                if ($bilty->to_location_id) {
                    $loc = DB::table('locations')->where('id', $bilty->to_location_id)->first();
                    if ($loc && !empty($loc->city_id)) {
                        $toCityId = $loc->city_id;
                    } else {
                        // Check if to_location_id was directly referencing a city id
                        $city = DB::table('cities')->where('id', $bilty->to_location_id)->first();
                        if ($city) {
                            $toCityId = $city->id;
                        } elseif ($loc && !empty($loc->name)) {
                            $cityByName = DB::table('cities')->where('name', 'like', '%' . trim($loc->name) . '%')->first();
                            if ($cityByName) $toCityId = $cityByName->id;
                        }
                    }
                }

                // Derive States and Countries from Cities
                if ($fromCityId) {
                    $c = DB::table('cities')->where('id', $fromCityId)->first();
                    if ($c && $c->state_id) {
                        $fromStateId = $c->state_id;
                        $s = DB::table('states')->where('id', $c->state_id)->first();
                        if ($s && $s->country_id) $fromCountryId = $s->country_id;
                    }
                }

                if ($toCityId) {
                    $c = DB::table('cities')->where('id', $toCityId)->first();
                    if ($c && $c->state_id) {
                        $toStateId = $c->state_id;
                        $s = DB::table('states')->where('id', $c->state_id)->first();
                        if ($s && $s->country_id) $toCountryId = $s->country_id;
                    }
                }

                DB::table('bilties')->where('id', $bilty->id)->update([
                    'from_city_id' => $fromCityId,
                    'to_city_id' => $toCityId,
                    'from_state_id' => $fromStateId,
                    'to_state_id' => $toStateId,
                    'from_country_id' => $fromCountryId,
                    'to_country_id' => $toCountryId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bilties')) {
            Schema::table('bilties', function (Blueprint $table) {
                $table->dropForeign(['from_city_id']);
                $table->dropForeign(['to_city_id']);
                $table->dropForeign(['from_state_id']);
                $table->dropForeign(['to_state_id']);
                $table->dropForeign(['from_country_id']);
                $table->dropForeign(['to_country_id']);

                $table->dropColumn([
                    'from_city_id',
                    'to_city_id',
                    'from_state_id',
                    'to_state_id',
                    'from_country_id',
                    'to_country_id'
                ]);
            });
        }

        if (Schema::hasTable('locations') && Schema::hasColumn('locations', 'city_id')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            });
        }
    }
};
