<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Lightweight liveness/readiness probe for uptime monitors and load balancers.
 *
 * Returns JSON (no views, so the global view composer never runs) and a 503 if
 * any dependency is unhealthy, so an orchestrator can pull the node out.
 */
class HealthController extends Controller
{
    public function __invoke()
    {
        $checks = [
            'database' => $this->check(fn () => DB::select('select 1')),
            'cache'    => $this->check(function () {
                $key = 'health:' . Str::random(8);
                Cache::put($key, '1', 5);
                $ok = Cache::get($key) === '1';
                Cache::forget($key);
                if (!$ok) {
                    throw new \RuntimeException('cache read-back failed');
                }
            }),
            'storage'  => $this->check(fn () => is_writable(storage_path('app'))
                ? null
                : throw new \RuntimeException('storage/app not writable')),
        ];

        $healthy = !in_array(false, array_column($checks, 'ok'), true);

        return response()->json([
            'status'    => $healthy ? 'ok' : 'error',
            'timestamp' => now()->toIso8601String(),
            'checks'    => $checks,
        ], $healthy ? 200 : 503);
    }

    /** Run one probe, capturing failure without leaking internals. */
    private function check(callable $probe): array
    {
        try {
            $probe();
            return ['ok' => true];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => class_basename($e)];
        }
    }
}
