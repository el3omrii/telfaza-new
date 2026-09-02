<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SoketiMetrics
{
    /**
     * Collect usage and metrics from the Soketi metrics server.
     *
     * The metrics server exposes "GET /usage" (JSON) and "GET /metrics"
     * (Prometheus plaintext) on its own port (default 9601).
     */
    public function stats(): array
    {
        if (! env('METRICS_ENABLED', true)) {
            return ['enabled' => false, 'online' => false];
        }

        $host = rtrim(env('METRICS_HOST', 'http://127.0.0.1:9601'), '/');

        try {
            $usage = Http::timeout(5)->get($host.'/usage');
            $metrics = Http::timeout(5)->get($host.'/metrics');

            $total = fn (string $key): float => (float) $this->parsePrometheus($key, $metrics->body())->sum('value');

            $startedAt = $this->parsePrometheus('soketi_process_start_time_seconds', $metrics->body())->value('value');

            return [
                'enabled'          => true,
                'online'           => true,
                'connections'      => (int) $total('soketi_connected'),
                'messagesSent'     => (int) $total('soketi_ws_messages_sent_total'),
                'messagesReceived' => (int) $total('soketi_ws_messages_received_total'),
                'bytesSent'        => $total('soketi_socket_transmitted_bytes'),
                'bytesReceived'    => $total('soketi_socket_received_bytes'),
                'httpCalls'        => (int) $total('soketi_http_calls_received_total'),
                'startedAt'        => $startedAt !== null ? now()->subSeconds(time() - (float) $startedAt) : null,
                'memory'           => [
                    'percent'    => (float) $usage->json('memory.percent', 0),
                    'total'      => (float) $usage->json('memory.total', 0),
                    'totalHuman' => $this->formatBytes((float) $usage->json('memory.total', 0)),
                ],
            ];
        } catch (\Throwable) {
            return ['enabled' => true, 'online' => false];
        }
    }

    /**
     * Format a number of bytes into a human readable string.
     */
    protected function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 1).' '.$units[$index];
    }

    /**
     * Parse a Prometheus plaintext exposition into key/label/value pairs.
     */
    protected function parsePrometheus(string $key, string $metrics): Collection
    {
        preg_match_all('`^('.$key.'){(.+?)}\s+([0-9.eE+.-]+)$`im', $metrics, $matches, PREG_SET_ORDER);

        return collect($matches)->map(function (array $item) {
            $labels = [];

            foreach (explode(',', $item[2]) as $pair) {
                [$name, $value] = explode('=', $pair, 2);
                $labels[$name] = trim($value, '"');
            }

            return ['key' => $item[1], 'json' => $labels, 'value' => $item[3]];
        });
    }
}
