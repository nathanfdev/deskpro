<?php

$ENV_CONFIG = [];

$ENV_CONFIG['set_umask'] = 0000;
$ENV_CONFIG['environment'] = 'dev';
$ENV_CONFIG['debug_mode'] = true;

// Paths to files that will be automatically included during env init
$ENV_CONFIG['init_scripts'] = [];

// Deskpro http and token exchange proxy URLs used in cloud mode
$ENV_CONFIG['proxy_token_exchange_url'] = getenv('DP_PROXY_TOKEN_EXCHANGE_URL') ?: "http://localhost:6317";
$ENV_CONFIG['http_proxy_url'] = getenv('DP_HTTP_PROXY_URL') ?: "http://localhost:3000";
