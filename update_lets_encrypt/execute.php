<?php
/**
 * Let's Encrypt を更新する
 *
 * @var array $config
 */

$config = [];
require_once __DIR__ . '/config.php';

foreach($config['certbots'] as $certbot) {

    // 実行対象でなければ
    if (empty($certbot['execution'])) {
        continue;
    }

    if (empty($certbot['domain'])) {
        continue;
    }

    // _acme-challengeをクリア
    $command = __DIR__ . '/' . $certbot['dns'] . '_acme_challenge_clear.sh';

    if ( ! file_exists($command)) {
        continue;
    }

    $command .= ' "' . $certbot['domain'] . '" "' . $config['php_command'] . '"';
    echo $command . "\n";

    $output = shell_exec($command);

    echo $output . "\n";
}
