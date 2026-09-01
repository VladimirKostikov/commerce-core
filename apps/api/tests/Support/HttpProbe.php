<?php

namespace Tests\Support;

final class HttpProbe
{
    public static function get(string $url, int $timeout = 5, array $headers = ['Accept: application/json']): array
    {
        $headerLine = $headers === [] ? '' : implode("\r\n", $headers)."\r\n";

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'ignore_errors' => true,
                'header' => $headerLine,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);

        return [
            'status' => self::statusFromHeaders($http_response_header ?? []),
            'body' => $body === false ? '' : $body,
        ];
    }

    public static function post(string $url, string $body = '', int $timeout = 5): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => $timeout,
                'ignore_errors' => true,
                'header' => "Content-Type: text/plain\r\nContent-Length: ".strlen($body)."\r\nAccept: */*\r\n",
                'content' => $body,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);

        return [
            'status' => self::statusFromHeaders($http_response_header ?? []),
            'body' => $responseBody === false ? '' : $responseBody,
        ];
    }

    public static function json(string $method, string $url, array $payload = [], array $headers = [], int $timeout = 5): array
    {
        $body = $payload === [] ? '' : json_encode($payload, JSON_THROW_ON_ERROR);
        $headerLine = implode("\r\n", array_merge([
            'Content-Type: application/json',
            'Accept: application/json',
            'Content-Length: '.strlen($body),
        ], $headers))."\r\n";

        $context = stream_context_create([
            'http' => [
                'method' => strtoupper($method),
                'timeout' => $timeout,
                'ignore_errors' => true,
                'header' => $headerLine,
                'content' => $body,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);

        return [
            'status' => self::statusFromHeaders($http_response_header ?? []),
            'body' => $responseBody === false ? '' : $responseBody,
        ];
    }

    private static function statusFromHeaders(array $headers): int
    {
        if ($headers === [] || ! preg_match('/\s(\d{3})\s/', $headers[0], $matches)) {
            return 0;
        }

        return (int) $matches[1];
    }
}
