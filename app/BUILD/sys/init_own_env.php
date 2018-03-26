<?php

// the env for THIS build directory.

define('DP_USE_BUILD_NAME', basename(realpath(__DIR__.'/../')));
require __DIR__.'/../../run/init_env.php';
