<?php
/**
 * DNSを更新する : Value Domain
 *
 * @var int $argc
 * @var array $argv
 * @var array $config
 */

$config = [];
require_once __DIR__ . '/config.php';

// 使用しないものは消しておく
unset($config['certbots']);

define('LOG_PATH', $config['log_path']);

/**
 * ログ
 *
 * @param string $message
 */
function writeLog(string $message): void
{
    if (empty(LOG_PATH)) {
        return;
    }

    if ( ! file_exists(dirname(LOG_PATH))) {
        return;
    }

    if ( ! is_writeable(dirname(LOG_PATH))) {
        return;
    }

    if (file_exists(LOG_PATH)) {
        if ( ! is_writeable(LOG_PATH)) {
            return;
        }
    }

    file_put_contents(LOG_PATH, date('Y-m-d H:i:s') . ' ' . $message . "\n", FILE_APPEND | LOCK_EX);
    echo $message . "\n";
}

$actions = [];
$actions[] = 'add';
$actions[] = 'clear';

$record_names = [];
$record_names[] = '_acme-challenge';

$action = '';
$domain = '';
$record_name = '';
$record_value = '';

if ($argc >= 2) {
    $action = (string) $argv[1];
}

if ($argc >= 3) {
    $domain = (string) $argv[2];
}

if ($argc >= 4) {
    $record_name = (string) $argv[3];
}

if ($argc >= 5) {
    $record_value = (string) $argv[4];
}

if ( ! in_array($action, $actions, true)) {
    writeLog('不正なパラメータ (1)');
    exit;
}

if ( ! preg_match('/\A[a-z0-9\.-]+\z/', $domain)) {
    writeLog('不正なパラメータ (2)');
    exit;
}

if ( ! in_array($record_name, $record_names, true)) {
    writeLog('不正なパラメータ (3)');
    exit;
}

if ($action === 'add') {
    if (empty($record_value)) {
        writeLog('不正なパラメータ (4)');
        exit;
    }
}

$api_url = 'https://api.value-domain.com/v1/domains/' . $domain . '/dns';

$headers = [
    'Authorization: Bearer ' . $config['value_domain_api_key'],
    'Content-Type: application/json'
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if(curl_errno($ch)) {
    writeLog('cURLエラー: ' . curl_error($ch));
    exit;
}
curl_close($ch);

$dns_records = json_decode($response, true);

if (isset($dns_records['error'])) {
    writeLog('DNSレコードの取得に失敗しました: ' . print_r($dns_records['error'], true));
    exit;
}

if (empty($dns_records['results'])) {
    writeLog('DNSレコードの取得に失敗しました: results が空');
    exit;
}

// echo print_r($dns_records, true) . "\n";

$update_data = [];
$update_data['domain'] = $dns_records['results']['domainname'];
// $update_data['ns_type'] = $dns_records['results']['ns_type'];
// $update_data['ttl'] = $dns_records['results']['ttl'];

if ($action === 'add') {

    $update_data['records'] = $dns_records['results']['records'] . "\n" . 'txt ' . $record_name . ' ' . $record_value;

} elseif ($action === 'clear') {

    $records = $dns_records['results']['records'];
    $records = str_replace(["\r\n", "\r"], "\n", $records);
    $records = explode("\n", $records);

    $new_records = [];
    foreach($records as $record) {

        if (empty($record)) {
            continue;
        }

        if (strpos($record, 'txt ' . $record_name . ' ') === 0) {
            continue;
        }

        $new_records[] = $record;
    }

    $update_data['records'] = implode("\n", $new_records) . "\n";
}

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));

$update_response = curl_exec($ch);
if (curl_errno($ch)) {
    writeLog('cURLエラー: ' . curl_error($ch));
    exit;
}
curl_close($ch);

$update_response = json_decode($update_response, true);

if (isset($update_response['error'])) {
    writeLog('txt ' . $record_name . ' の更新に失敗しました: ' . print_r($update_response['error'], true));
    exit;
}

writeLog('txt ' . $record_name . ' を更新しました');
