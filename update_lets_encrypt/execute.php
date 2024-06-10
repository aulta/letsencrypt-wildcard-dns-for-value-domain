<?php
/**
 * Let's Encrypt を更新する
 *
 * @var array $config
 */

define('PATH_ACME_CHALLENGE_PATH', __DIR__ . '/config_acme_challenge.sh');

$config = [];
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utility.php';

// 現在の証明書情報
$certificates_info = shell_exec('certbot certificates 2>&1');
echo $certificates_info;
echo "\n";

foreach($config['certbots'] as $certbot) {

    // 実行対象でなければ
    if (empty($certbot['execution'])) {
        continue;
    }

    if (empty($certbot['mail'])) {
        continue;
    }

    if (empty($certbot['domain'])) {
        continue;
    }

    if (empty($certbot['domains'])) {
        continue;
    }

    $target_domain = $certbot['domain'];

    // 特定のドメインの有効期限を確認
    if (strpos($certificates_info, $target_domain) === false) {
        writeLog('ドメイン ' . $target_domain . ' の証明書が見つかりません');
        continue;
    }

    // 証明書の有効期限が30日以内かどうかを確認
    $need_renewal = false;
    $lines = explode("\n", $certificates_info);
    foreach ($lines as $line) {
        if (strpos($line, $target_domain) !== false) {

            // 次の行に有効期限が含まれている
            $line_index = array_search($line, $lines) + 1;
            $matches = [];
            if (isset($lines[$line_index]) && preg_match('/VALID:.*days/i', $lines[$line_index], $matches)) {

                // 有効期限の日数を抽出
                $day_match = [];
                if (preg_match('/(\d+)\s+days/i', $matches[0], $day_match)) {
                    $days_left = (int)$day_match[1];
                    if ($days_left <= 30) {
                        $need_renewal = true;
                    }
                }
            }
            break;
        }
    }

    if (!$need_renewal) {
        writeLog('証明書の更新は必要ありません (ドメイン: ' . $target_domain . ')');
        continue;
    }

    continue;

    //
    $config_acme_challenge = [];

    $config_acme_challenge[] = '#!/bin/bash';
    $config_acme_challenge[] = '';
    $config_acme_challenge[] = '# このファイルは自動生成されます。変更しないでください';
    $config_acme_challenge[] = '# 最終実行 : ' . date('Y-m-d H:i:s');
    $config_acme_challenge[] = '';
    $config_acme_challenge[] = 'CONFIG_DOMAIN="' . $certbot['domain'] . '"';
    $config_acme_challenge[] = 'CONFIG_PHP_COMMAND="' . $config['php_command'] . '"';
    $config_acme_challenge[] = 'CONFIG_WAIT_DNS_SECONDS=' . $config['wait_for_dns_propagation'];
    $config_acme_challenge[] = '';

    $config_path = __DIR__ . '/config.sh';
    $data = implode("\n", $config_acme_challenge) . "\n";
    file_put_contents($config_path, $data);

    // _acme-challengeをクリア
    if (true) {

        $command_clear = __DIR__ . '/' . $certbot['dns'] . '_acme_challenge_clear.sh';

        if ( ! file_exists($command_clear)) {
            continue;
        }

        echo $command_clear . "\n";

        $output = shell_exec($command_clear);
        echo $output . "\n";
    }

    # certbotを実行して証明書を取得
    do {

        $c = [];
        $c[] = 'certbot certonly';
        $c[] = '--manual';
        $c[] = '--server https://acme-v02.api.letsencrypt.org/directory';
        $c[] = '--preferred-challenges dns';

        foreach($certbot['domains'] as $domain) {
            $c[] = '-d ' . $domain;
        }

        $c[] = '-m ' . $certbot['mail'];
        $c[] = '--agree-tos';
        $c[] = '--manual-auth-hook "' . __DIR__ . '/' . $certbot['dns'] . '_acme_challenge_update.sh"';
        $c[] = '--manual-cleanup-hook "' . __DIR__ . '/' . $certbot['dns'] . '_acme_challenge_clear.sh"';

        // $c[] = '--force-renewal'; // 証明書の有効期限に関わらず更新を強制する

        echo implode("\n", $c) . "\n";

        $command = implode(' ', $c);
        $output = shell_exec($command);
        echo $output . "\n";

    } while(false);

}
