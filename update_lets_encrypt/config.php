<?php
/**
 * 設定ファイル
 *
 * @var array $config
 */

$config = [];

// APIキー
$config['value_domain_api_key'] = 'バリュードメインで発行したAPIキー';

// PHP
$config['php_command'] = 'php';

// https://aulta.co.jp/technical/server-build/centos7/php/source-install-php-7-4-6
// $config['php_command'] = '/usr/local/lib/php-7.1.6-mysqlc-mysqlnd/bin/php-7.1.6-mysqlc-mysqlnd';

// ログ
$config['log_path'] = '/var/log/update_lets_encrypt_' . date('Y') . '.log';

// ドメイン
$config['certbots'] = [];

$config['certbots'][] = [
    'execution' => true,
    'dns' => 'value_domain',
    'mail' => 'user@example.com',
    'domain' => 'example.com',
    'domains' => [
        '*.example.com',
        'example.com'
    ]
];

$config['certbot_domains'][] = [
    'execution' => true,
    'dns' => 'value_domain',
    'mail' => 'user@example.net',
    'domain' => 'example.net',
    'domains' => [
        '*.example.net'
    ]
];

$config['certbot_domains'][] = [
    'execution' => true,
    'dns' => 'value_domain',
    'mail' => 'user@example.jp',
    'domain' => 'example.jp',
    'domains' => [
        'example.jp'
    ]
];

