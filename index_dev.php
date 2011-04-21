<?php
require('./dppath.php');
require(DP_ROOT . '/sys/Kernel/Boot.php');
\DeskPRO\Kernel\Boot::bootWeb('dev', true);
