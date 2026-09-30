<?php

namespace App\Services;

use App\Models\GstCache;
use App\Models\StateModel;
use App\Models\CityModel;
use App\Models\AccountLedger;
use App\Models\Party;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GstService
{
    /**
     * Map of Indian GST State Codes (2 digits)
     */
    public const STATE_CODES = [
        '01' => 'JAMMU AND KASHMIR',
        '02' => 'HIMACHAL PRADESH',
        '03' => 'PUNJAB',
        '04' => 'CHANDIGARH',
        '05' => 'UTTARAKHAND',
        '06' => 'HARYANA',
        '07' => 'DELHI',
        '08' => 'RAJASTHAN',
        '09' => 'UTTAR PRADESH',
        '10' => 'BIHAR',
        '11' => 'SIKKIM',
        '12' => 'ARUNACHAL PRADESH',
        '13' => 'NAGALAND',
        '14' => 'MANIPUR',
        '15' => 'MIZORAM',
        '16' => 'TRIPURA',
        '17' => 'MEGHALAYA',
        '18' => 'ASSAM',
        '19' => 'WEST BENGAL',
        '20' => 'JHARKHAND',
        '21' => 'ODISHA',
        '22' => 'CHHATTISGARH',
        '23' => 'MADHYA PRADESH',
        '24' => 'GUJARAT',
        '25' => 'DAMAN AND DIU',
        '26' => 'DADRA AND NAGAR HAVELI',
        '27' => 'MAHARASHTRA',
        '28' => 'ANDHRA PRADESH',
        '29' => 'KARNATAKA',
        '30' => 'GOA',
        '31' => 'LAKSHADWEEP',
        '32' => 'KERALA',
        '33' => 'TAMIL NADU',
        '34' => 'PUDUCHERRY',
        '35' => 'ANDAMAN AND NICOBAR ISLANDS',
        '36' => 'TELANGANA',
        '37' => 'ANDHRA PRADESH (NEW)',
        '38' => 'LADAKH',
        '97' => 'OTHER TERRITORY',
    ];

    /**
     * PAN 4th Character Entity Type map
     */
    public const ENTITY_TYPES = [
        'C' => 'Company (Private / Public Limited)',
        'P' => 'Proprietorship / Individual',
        'H' => 'Hindu Undivided Family (HUF)',
        'F' => 'Partnership Firm / LLP',
        'A' => 'Association of Persons (AOP)',
        'T' => 'Trust',
        'B' => 'Body of Individuals (BOI)',
        'L' => 'Local Authority',
        'J' => 'Artificial Juridical Person',
        'G' => 'Government Agency',
    ];

    /**
     * Validate GSTIN format
     */
    public function isValidGstin(string $gstin): bool
    {
        $gstin = strtoupper(trim($gstin));
        return (bool) preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstin);
    }

    /**
     * Lookup GSTIN details with multi-source fallback and smart caching
     */
    public function lookup(string $gstin, bool $forceRefresh = false): array
    {
        $gstin = strtoupper(trim($gstin));

        if (strlen($gstin) !== 15 || !$this->isValidGstin($gstin)) {
            return [
                'success' => false,
                'message' => 'Invalid GSTIN format. Must be 15 alphanumeric characters (e.g., 18AAHCD3526G2ZP).',
                'gstin'   => $gstin,
            ];
        }

        $stateCode = substr($gstin, 0, 2);
        $pan       = substr($gstin, 2, 10);
        $panTypeChar = substr($pan, 3, 1);
        $constitutionHint = self::ENTITY_TYPES[$panTypeChar] ?? 'Business Entity';
        $stateNameFromCode = self::STATE_CODES[$stateCode] ?? 'ASSAM';

        // 1. Check local smart cache
        if (!$forceRefresh) {
            $cached = GstCache::where('gstin', $gstin)->first();
            if ($cached) {
                return $this->formatResponse($cached->toArray(), 'cache');
            }
        }

        // 2. Try Live GST APIs if configured or public gateways
        $liveData = $this->fetchFromLiveApi($gstin);
        if ($liveData && !empty($liveData['legal_name'])) {
            $saved = GstCache::updateOrCreate(
                ['gstin' => $gstin],
                array_merge($liveData, [
                    'source'          => 'live_api',
                    'last_checked_at' => now(),
                ])
            );
            return $this->formatResponse($saved->toArray(), 'live_api');
        }

        // 3. Try finding in existing Account Ledgers or Parties
        $existingLedger = AccountLedger::where('gst_no', $gstin)->first();
        if ($existingLedger) {
            $ledgerData = [
                'gstin'         => $gstin,
                'legal_name'    => $existingLedger->ledger_name,
                'trade_name'    => $existingLedger->alias_name ?: $existingLedger->ledger_name,
                'status'        => 'Active',
                'taxpayer_type' => 'Regular',
                'constitution'  => $constitutionHint,
                'address'       => $existingLedger->address,
                'city'          => $existingLedger->cityRelation ? $existingLedger->cityRelation->name : null,
                'district'      => null,
                'state_name'    => $existingLedger->stateRelation ? $existingLedger->stateRelation->name : $stateNameFromCode,
                'state_code'    => $stateCode,
                'pincode'       => $existingLedger->pin_code,
                'pan'           => $pan,
                'source'        => 'local_ledger',
                'last_checked_at' => now(),
            ];
            $saved = GstCache::updateOrCreate(['gstin' => $gstin], $ledgerData);
            return $this->formatResponse($saved->toArray(), 'local_ledger');
        }

        // 4. Fallback algorithm & state resolver
        $fallbackData = [
            'gstin'         => $gstin,
            'legal_name'    => null,
            'trade_name'    => null,
            'status'        => 'Active',
            'taxpayer_type' => 'Regular',
            'constitution'  => $constitutionHint,
            'address'       => null,
            'city'          => null,
            'district'      => null,
            'state_name'    => $stateNameFromCode,
            'state_code'    => $stateCode,
            'pincode'       => null,
            'pan'           => $pan,
            'source'        => 'algorithm',
            'last_checked_at' => now(),
        ];

        return $this->formatResponse($fallbackData, 'algorithm');
    }

    /**
     * Try live GST API endpoints
     */
    protected function fetchFromLiveApi(string $gstin): ?array
    {
        $stateCode = substr($gstin, 0, 2);
        $pan       = substr($gstin, 2, 10);
        $panTypeChar = substr($pan, 3, 1);
        $constitutionHint = self::ENTITY_TYPES[$panTypeChar] ?? 'Business Entity';

        // Check if Sandbox.co.in API credentials are set
        $sandboxKey = config('services.gst.sandbox_key') ?? env('SANDBOX_GST_KEY');
        $sandboxSecret = config('services.gst.sandbox_secret') ?? env('SANDBOX_GST_SECRET');
        if ($sandboxKey && $sandboxSecret) {
            try {
                $response = Http::withHeaders([
                    'x-api-key'     => $sandboxKey,
                    'x-api-secret'  => $sandboxSecret,
                    'x-api-version' => '1.0',
                ])->timeout(4)->get("https://api.sandbox.co.in/gst/compliance/public/taxpayer/{$gstin}");

                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['data'] ?? $json;
                    if (!empty($data['lgnm']) || !empty($data['legal_name'])) {
                        return $this->normalizeApiData($data, $gstin, $json);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Sandbox GST API lookup failed: " . $e->getMessage());
            }
        }

        // Check RapidAPI GSTIN Verification
        $rapidApiKey = config('services.gst.rapidapi_key') ?? env('RAPIDAPI_GST_KEY');
        if ($rapidApiKey) {
            try {
                $response = Http::withHeaders([
                    'X-RapidAPI-Key'  => $rapidApiKey,
                    'X-RapidAPI-Host' => 'gst-verification.p.rapidapi.com',
                ])->timeout(4)->get("https://gst-verification.p.rapidapi.com/v1/gstin/{$gstin}");

                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['data'] ?? $json;
                    if (!empty($data['lgnm']) || !empty($data['legal_name'])) {
                        return $this->normalizeApiData($data, $gstin, $json);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("RapidAPI GST lookup failed: " . $e->getMessage());
            }
        }

        // Check Custom GSP / Public Webhook URL if configured in .env
        $customApiUrl = env('GST_API_URL');
        if ($customApiUrl) {
            try {
                $url = str_replace('{gstin}', $gstin, $customApiUrl);
                $response = Http::timeout(4)->get($url);
                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['data'] ?? $json;
                    if (!empty($data['lgnm']) || !empty($data['legal_name'])) {
                        return $this->normalizeApiData($data, $gstin, $json);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Custom GST API lookup failed: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Normalize various API response structures into standard fields
     */
    protected function normalizeApiData(array $d, string $gstin, array $raw): array
    {
        $legalName = $d['lgnm'] ?? $d['legal_name'] ?? $d['tradeNam'] ?? $d['trade_name'] ?? '';
        $tradeName = $d['tradeNam'] ?? $d['trade_name'] ?? $legalName;
        $status    = $d['sts'] ?? $d['status'] ?? 'Active';
        $taxType   = $d['dty'] ?? $d['taxpayer_type'] ?? 'Regular';
        $ctb       = $d['ctb'] ?? $d['constitution'] ?? '';

        // Address parsing
        $pradr = $d['pradr']['addr'] ?? $d['principal_address'] ?? $d['address'] ?? [];
        $addrParts = [];
        if (is_array($pradr)) {
            if (!empty($pradr['bno'])) $addrParts[] = $pradr['bno'];
            if (!empty($pradr['bnm'])) $addrParts[] = $pradr['bnm'];
            if (!empty($pradr['st']))  $addrParts[] = $pradr['st'];
            if (!empty($pradr['loc'])) $addrParts[] = $pradr['loc'];
            if (!empty($pradr['dst'])) $addrParts[] = $pradr['dst'];
            if (!empty($pradr['stcd']))$addrParts[] = $pradr['stcd'];
            if (!empty($pradr['pncd']))$addrParts[] = $pradr['pncd'];
            $fullAddress = implode(', ', array_filter($addrParts));
            $city = $pradr['dst'] ?? $pradr['loc'] ?? $pradr['city'] ?? null;
            $pincode = $pradr['pncd'] ?? $pradr['pincode'] ?? null;
            $district = $pradr['dst'] ?? null;
        } else {
            $fullAddress = (string)$pradr;
            $city = $d['city'] ?? null;
            $pincode = $d['pincode'] ?? null;
            $district = null;
        }

        $stateCode = substr($gstin, 0, 2);
        $stateName = self::STATE_CODES[$stateCode] ?? ($d['state'] ?? 'ASSAM');

        return [
            'gstin'         => $gstin,
            'legal_name'    => trim($legalName),
            'trade_name'    => trim($tradeName),
            'status'        => trim($status),
            'taxpayer_type' => trim($taxType),
            'constitution'  => trim($ctb),
            'address'       => trim($fullAddress),
            'city'          => $city ? trim($city) : null,
            'district'      => $district ? trim($district) : null,
            'state_name'    => trim($stateName),
            'state_code'    => $stateCode,
            'pincode'       => $pincode ? trim($pincode) : null,
            'pan'           => substr($gstin, 2, 10),
            'raw_response'  => $raw,
        ];
    }

    /**
     * Format response with database relations (StateModel and CityModel IDs)
     */
    protected function formatResponse(array $data, string $source): array
    {
        $stateCode = $data['state_code'] ?? substr($data['gstin'], 0, 2);
        
        // Resolve state_id from Database
        $stateRecord = StateModel::where('code', $stateCode)
            ->orWhere('name', 'LIKE', '%' . ($data['state_name'] ?? '') . '%')
            ->first();

        // Resolve city_id from Database if city is known
        $cityRecord = null;
        if (!empty($data['city'])) {
            $cityQuery = CityModel::where('name', 'LIKE', '%' . $data['city'] . '%');
            if ($stateRecord) {
                $cityQuery->where('state_id', $stateRecord->id);
            }
            $cityRecord = $cityQuery->first();
        }

        return [
            'success'       => true,
            'source'        => $source,
            'gstin'         => $data['gstin'],
            'legal_name'    => $data['legal_name'] ?? null,
            'trade_name'    => $data['trade_name'] ?? $data['legal_name'] ?? null,
            'status'        => $data['status'] ?? 'Active',
            'taxpayer_type' => $data['taxpayer_type'] ?? 'Regular',
            'constitution'  => $data['constitution'] ?? null,
            'address'       => $data['address'] ?? null,
            'city'          => $data['city'] ?? ($cityRecord ? $cityRecord->name : null),
            'city_id'       => $cityRecord ? $cityRecord->id : null,
            'district'      => $data['district'] ?? null,
            'state_name'    => $stateRecord ? $stateRecord->name : ($data['state_name'] ?? self::STATE_CODES[$stateCode] ?? 'ASSAM'),
            'state_code'    => $stateCode,
            'state_id'      => $stateRecord ? $stateRecord->id : null,
            'pincode'       => $data['pincode'] ?? null,
            'pan'           => $data['pan'] ?? substr($data['gstin'], 2, 10),
            'is_verified'   => in_array($source, ['live_api', 'cache', 'local_ledger']),
        ];
    }
}
