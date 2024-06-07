#!/bin/bash

ARG_SCRIPT_PATH=$(cd "$(dirname "$0")" && pwd)
ARG_DOMAIN=$1
ARG_PHP_COMMAND=$2

$ARG_PHP_COMMAND "$ARG_SCRIPT_PATH/value_domain_update_dns.php" clear $ARG_DOMAIN _acme-challenge
