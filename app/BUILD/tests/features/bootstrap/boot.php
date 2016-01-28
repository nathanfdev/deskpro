<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

define('DP_ROOT', __DIR__.'/../../../');
define('DP_WEB_ROOT', __DIR__.'/../../../../');
define('DP_CONFIG_FILE', __DIR__.'/../../config.test.php');
define('DP_TESTS_RUNNING', true);
define('DP_INTERFACE', 'user');
define('DP_TESTS_START_TIME', time());

require_once __DIR__.'/../../../sys/preboot.php';
require_once __DIR__.'/../../../sys/autoload.php';
require_once __DIR__.'/../../../sys/system.php';

set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor-src/php-utf8/');
