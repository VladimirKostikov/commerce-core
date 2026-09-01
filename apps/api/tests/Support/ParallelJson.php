<?php

namespace Tests\Support;

final class ParallelJson
{
    public static function post(string $url, array $payloads, int $timeout = 30): array
    {
        if ($payloads === []) {
            return [];
        }

        $dir = sys_get_temp_dir().'/pj_'.bin2hex(random_bytes(4));
        mkdir($dir, 0700);

        try {
            foreach (array_values($payloads) as $index => $payload) {
                file_put_contents(
                    $dir.'/'.$index.'.json',
                    json_encode($payload, JSON_THROW_ON_ERROR),
                );
            }

            $script = sprintf(
                'for i in $(seq 0 %d); do curl -sS -o /dev/null -w "%%{http_code}\\n" --max-time %d -X POST %s -H %s -H %s --data-binary @%s/$i.json & done; wait',
                count($payloads) - 1,
                $timeout,
                escapeshellarg($url),
                escapeshellarg('Content-Type: application/json'),
                escapeshellarg('Accept: application/json'),
                escapeshellarg($dir),
            );

            $codes = [];
            $exit = 0;
            exec('sh -c '.escapeshellarg($script), $codes, $exit);

            return $codes;
        } finally {
            foreach (glob($dir.'/*.json') ?: [] as $file) {
                unlink($file);
            }

            rmdir($dir);
        }
    }
}
