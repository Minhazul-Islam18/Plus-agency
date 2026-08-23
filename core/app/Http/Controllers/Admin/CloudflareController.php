<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtra;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareController extends Controller
{
    /**
     * Purges the entire Cloudflare cache for the configured zone — for when
     * an admin needs a content change to show immediately instead of
     * waiting out the Cache Rule's edge TTL. Credentials come from
     * admin/basicinfo (BasicExtra) if set there, falling back to
     * CLOUDFLARE_ZONE_ID/CLOUDFLARE_API_TOKEN in .env otherwise — same
     * "admin setting overrides static config" pattern as the LFM upload
     * limits on the same page.
     */
    public function purgeCache(Request $request)
    {
        $bex = BasicExtra::first();
        $zoneId = !empty($bex->cloudflare_zone_id) ? $bex->cloudflare_zone_id : config('services.cloudflare.zone_id');
        $apiToken = !empty($bex->cloudflare_api_token) ? $bex->cloudflare_api_token : config('services.cloudflare.api_token');

        if (empty($zoneId) || empty($apiToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Cloudflare isn\'t configured yet — set the Zone ID and API Token under Settings → Basic Info first.',
            ]);
        }

        try {
            $response = Http::withToken($apiToken)
                ->timeout(15)
                ->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", [
                    'purge_everything' => true,
                ]);

            $result = $response->json();

            if ($response->successful() && ($result['success'] ?? false)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cloudflare cache purged successfully.',
                ]);
            }

            $errorMessage = $result['errors'][0]['message'] ?? 'Cloudflare rejected the request (check the zone ID and API token).';
            Log::error('[Cloudflare] Cache purge failed', ['response' => $result]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            Log::error('[Cloudflare] Cache purge request failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Could not reach Cloudflare — ' . $e->getMessage(),
            ]);
        }
    }
}
