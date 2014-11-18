<?php
define('DP_BOOT_MODE', 'testing');
require(__DIR__. '/../../../index.php');
require(__DIR__. '/DpTestEnv.php');
DpTestEnv::init();
