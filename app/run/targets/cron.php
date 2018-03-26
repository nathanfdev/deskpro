<?php

require __DIR__.'/../init_env.php';
require DP_APP_DIR.'/sys/Boot/Boot.php';
\DpSys\Boot\Boot::bootCron($DP_ENV);
