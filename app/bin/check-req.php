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

define('DP_ROOT', realpath(dirname(__FILE__).'/../'));

@ini_set('memory_limit', -1);
@ini_set('memory_limit', 268435456);
@set_time_limit(0);

// Normalise env
setlocale(LC_CTYPE, 'C');
date_default_timezone_set('UTC');
ini_set('default_charset', 'UTF-8');

require_once DP_ROOT.'/src/Application/InstallBundle/Install/server_check_functions.php';
require_once DP_ROOT.'/src/Orb/Util/Numbers.php';
require_once DP_ROOT.'/src/Orb/Util/Env.php';

$fatal = array();

foreach (deskpro_install_check_reqs() as $type => $level) {
    if ($level == 'fatal') {
        $fatal[] = $type;
    }
}

if (!$fatal) {
    if (\Orb\Util\Env::isFunctionDisabled('exec')) {
        $fatal[] = 'Your server has disabled the PHP exec() function. The automatic upgrader requires this function.';
    }
}

if ($fatal) {
    echo 'Errors detected: '.implode(',', $fatal);
} else {
    echo 'OKAY';
}

echo "\n";
