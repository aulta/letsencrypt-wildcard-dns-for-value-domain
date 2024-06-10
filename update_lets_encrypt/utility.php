<?php
/**
 * ユーティリティ
 *
 * @var array $config
 */

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
