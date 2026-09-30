<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GstService;
use App\Models\GstCache;

class GstLookupController extends Controller
{
    protected GstService $gstService;

    public function __construct(GstService $gstService)
    {
        $this->gstService = $gstService;
    }

    /**
     * AJAX Endpoint: Lookup GSTIN Details
     * 
     * GET  /api/gst/lookup/{gstin}
     * POST /api/gst/lookup
     */
    public function lookup(Request $request, $gstin = null)
    {
        $gstin = $gstin ?: $request->input('gstin', $request->query('gstin'));
        $forceRefresh = $request->boolean('force_refresh', false);

        if (!$gstin) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a 15-character GSTIN.',
            ], 422);
        }

        $result = $this->gstService->lookup($gstin, $forceRefresh);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Optional: Clear or refresh cache for a GSTIN
     */
    public function clearCache(Request $request, $gstin)
    {
        $deleted = GstCache::where('gstin', strtoupper(trim($gstin)))->delete();
        return response()->json([
            'success' => true,
            'message' => $deleted ? 'GST cache cleared.' : 'No cache entry found.',
        ]);
    }
}
