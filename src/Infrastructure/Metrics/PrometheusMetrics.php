<?php

declare(strict_types=1);

namespace Infrastructure\Metrics;

/**
 * Minimal Prometheus text exposition format exporter.
 * Counters live in Redis so all Swarm replicas share metrics.
 */
final class PrometheusMetrics
{
    private const PREFIX = 'metrics:';

    public function __construct(
        private readonly \Predis\ClientInterface $redis,
        private readonly string $instance = 'app',
    ) {}

    public function increment(string $name, array $labels = [], float $value = 1.0): void
    {
        $key = self::PREFIX . 'counter:' . $name . ':' . $this->labelKey($labels);
        $this->redis->incrbyfloat($key, $value);
        $this->redis->hset(self::PREFIX . 'meta:counter:' . $name, $this->labelKey($labels), json_encode($labels));
    }

    public function observe(string $name, float $seconds, array $labels = []): void
    {
        $key = self::PREFIX . 'hist:' . $name . ':' . $this->labelKey($labels);
        $this->redis->rpush($key, (string) $seconds);
        $this->redis->ltrim($key, -1000, -1);
        $this->redis->hset(self::PREFIX . 'meta:hist:' . $name, $this->labelKey($labels), json_encode($labels));
    }

    public function render(): string
    {
        $lines = [
            '# HELP app_info Application instance metadata',
            '# TYPE app_info gauge',
            sprintf('app_info{instance="%s",service="product-platform"} 1', $this->escape($this->instance)),
            '# HELP http_requests_total Total HTTP requests',
            '# TYPE http_requests_total counter',
        ];

        foreach ($this->redis->keys(self::PREFIX . 'counter:*') as $key) {
            $value = (float) $this->redis->get($key);
            $parts = explode(':', (string) $key, 4);
            $name = $parts[2] ?? 'unknown';
            $labelPart = $parts[3] ?? '';
            $labels = $this->decodeLabels($name, 'counter', $labelPart);
            $labels['instance'] = $this->instance;
            $lines[] = sprintf('%s{%s} %s', $name, $this->formatLabels($labels), $value);
        }

        $lines[] = '# HELP http_request_duration_seconds Request duration samples (avg of last N)';
        $lines[] = '# TYPE http_request_duration_seconds gauge';

        foreach ($this->redis->keys(self::PREFIX . 'hist:*') as $key) {
            $samples = $this->redis->lrange($key, 0, -1);
            if ($samples === []) {
                continue;
            }
            $avg = array_sum(array_map('floatval', $samples)) / count($samples);
            $parts = explode(':', (string) $key, 4);
            $name = $parts[2] ?? 'unknown';
            $labelPart = $parts[3] ?? '';
            $labels = $this->decodeLabels($name, 'hist', $labelPart);
            $labels['instance'] = $this->instance;
            $lines[] = sprintf('%s{%s} %s', $name, $this->formatLabels($labels), $avg);
        }

        $lines[] = '# HELP php_memory_bytes Current PHP memory usage';
        $lines[] = '# TYPE php_memory_bytes gauge';
        $lines[] = sprintf(
            'php_memory_bytes{instance="%s"} %d',
            $this->escape($this->instance),
            memory_get_usage(true),
        );

        return implode("\n", $lines) . "\n";
    }

    /** @param array<string, string|int> $labels */
    private function labelKey(array $labels): string
    {
        ksort($labels);
        return md5(json_encode($labels) ?: '');
    }

    /** @return array<string, string> */
    private function decodeLabels(string $name, string $type, string $hash): array
    {
        $raw = $this->redis->hget(self::PREFIX . "meta:{$type}:{$name}", $hash);
        if (!$raw) {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string, string|int> $labels */
    private function formatLabels(array $labels): string
    {
        $parts = [];
        foreach ($labels as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $this->escape((string) $v));
        }
        return implode(',', $parts);
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', "\n", '"'], ['\\\\', '\\n', '\\"'], $value);
    }
}
