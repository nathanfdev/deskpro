<?php
define('DP_BOOT_MODE', 'testing');
require(realpath(__DIR__. '/../../index.php'));
require(realpath(__DIR__. '/DpTestControl.php'));
DpTestControl::init();