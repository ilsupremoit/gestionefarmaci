<?php

declare(strict_types=1);

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Repositories\MemoryRepository;

return [

    'default_connection' => env('MQTT_CONNECTION', 'default'),

    'connections' => [

        'default' => [

            'host' => env('MQTT_HOST'),
            'port' => env('MQTT_PORT', 1883),

            'protocol' => MqttClient::MQTT_3_1,

            'client_id' => env('MQTT_CLIENT_ID'),

            'use_clean_session' => env('MQTT_CLEAN_SESSION', true),

            'enable_logging' => env('MQTT_ENABLE_LOGGING', true),

            'log_channel' => env('MQTT_LOG_CHANNEL', null),

            'repository' => MemoryRepository::class,

            'connection_settings' => [

                'tls' => [
                    'enabled' => env('MQTT_TLS_ENABLED', false),
                    'allow_self_signed_certificate' => env('MQTT_TLS_ALLOW_SELF_SIGNED_CERT', false),
                    'verify_peer' => env('MQTT_TLS_VERIFY_PEER', true),
                    'verify_peer_name' => env('MQTT_TLS_VERIFY_PEER_NAME', true),
                    'ca_file' => env('MQTT_TLS_CA_FILE'),
                    'ca_path' => env('MQTT_TLS_CA_PATH'),
                    'client_certificate_file' => env('MQTT_TLS_CLIENT_CERT_FILE'),
                    'client_certificate_key_file' => env('MQTT_TLS_CLIENT_CERT_KEY_FILE'),
                    'client_certificate_key_passphrase' => env('MQTT_TLS_CLIENT_CERT_KEY_PASSPHRASE'),
                    'alpn' => env('MQTT_TLS_ALPN'),
                ],

                'auth' => [
                    'username' => env('MQTT_AUTH_USERNAME'),
                    'password' => env('MQTT_AUTH_PASSWORD'),
                ],

                'connect_timeout' => env('MQTT_CONNECT_TIMEOUT', 60),
                'socket_timeout' => env('MQTT_SOCKET_TIMEOUT', 5),
                'resend_timeout' => env('MQTT_RESEND_TIMEOUT', 10),
                'keep_alive_interval' => env('MQTT_KEEP_ALIVE_INTERVAL', 10),
            ],
        ],
    ],
];
