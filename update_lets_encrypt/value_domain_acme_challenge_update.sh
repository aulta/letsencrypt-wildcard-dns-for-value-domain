#!/bin/bash

ARG_SCRIPT_PATH=$(cd "$(dirname "$0")" && pwd)

source $ARG_SCRIPT_PATH/config.sh

$CONFIG_PHP_COMMAND "$ARG_SCRIPT_PATH/value_domain_update_dns.php" add $CONFIG_DOMAIN _acme-challenge "$CERTBOT_VALIDATION"

# DNSの反映を待つ
sleep $CONFIG_WAIT_DNS_SECONDS
