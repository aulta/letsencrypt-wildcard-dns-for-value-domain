#!/bin/bash

# ターゲットドメインを定義
TARGET_DOMAIN=example.com

# _acme-challengeをクリア
php /path/to/update_lets_encrypt.php clear $TARGET_DOMAIN _acme-challenge

# certbotを実行して証明書を取得
certbot certonly \
--manual \
--server https://acme-v02.api.letsencrypt.org/directory \
--preferred-challenges dns \
-d *.$TARGET_DOMAIN \
-d $TARGET_DOMAIN \
-m sample@example.com \
--agree-tos \
--manual-auth-hook "/path/to/update_acme_challenge.sh" \
--manual-cleanup-hook "/path/to/clear_acme_challenge.sh"
