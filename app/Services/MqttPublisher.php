<?php

namespace App\Services;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttPublisher
{
    public function publish(string $topic, array|string $payload, int $qos = 1, bool $retain = false): void
    {
        $host = env('MQTT_HOST');
        $port = (int) env('MQTT_PORT', 8883);

        $clientId = 'laravel-web-' . uniqid();

        $settings = (new ConnectionSettings)
            ->setUsername(env('MQTT_AUTH_USERNAME'))
            ->setPassword(env('MQTT_AUTH_PASSWORD'))
            ->setUseTls((bool) env('MQTT_TLS_ENABLED', true))
            ->setTlsSelfSignedAllowed((bool) env('MQTT_TLS_ALLOW_SELF_SIGNED_CERT', false))
            ->setConnectTimeout((int) env('MQTT_CONNECT_TIMEOUT', 10))
            ->setSocketTimeout((int) env('MQTT_SOCKET_TIMEOUT', 5))
            ->setKeepAliveInterval((int) env('MQTT_KEEP_ALIVE_INTERVAL', 60));

        $mqtt = new MqttClient($host, $port, $clientId);

        try {
            $mqtt->connect($settings, true);

            $message = is_array($payload)
                ? json_encode($payload, JSON_UNESCAPED_UNICODE)
                : $payload;

            $mqtt->publish($topic, $message, $qos, $retain);
        } finally {
            try {
                $mqtt->disconnect();
            } catch (\Throwable $e) {
            }
        }
    }
}
