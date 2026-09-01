<?php

namespace Tests\Support;

final class GrafanaProbe
{
    public static function get(string $path): array
    {
        $user = (string) (getenv('GRAFANA_USER') ?: 'admin');
        $password = (string) (getenv('GRAFANA_PASSWORD') ?: 'admin');

        return HttpProbe::get(
            InfrastructureHost::grafanaUrl().$path,
            10,
            [
                'Authorization: Basic '.base64_encode($user.':'.$password),
                'Accept: application/json',
            ],
        );
    }
}
