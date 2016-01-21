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

$DP_CONFIG = array('debug' => [], 'SETTINGS' => []);
define('DP_DATABASE_HOST', 'localhost');
define('DP_DATABASE_USER', 'root');
define('DP_DATABASE_PASSWORD', 'deskpro');
define('DP_TECHNICAL_EMAIL', 'dev@deskprodev.com');

function dev_config_read_file($f)
{
    $f = explode("\n", trim(file_get_contents(__DIR__.'/'.$f)));
    $f = array_filter($f, function ($l) { return !($l[0] === '#' || $l[0] === ';' || trim($l[0]) === ''); });

    return trim($f[0]);
}

// DB name from a text file -- makes it easy to automate changing it
// e.g. if you want to test an upgrade script multiple times, its easy
// to script a bash file to write a new dbname here etc
define('DP_DATABASE_NAME', dev_config_read_file('DB_NAME.txt'));

// Often useful to override this setting than to always update it after new installs, or db imports, etc
// The dev fixture will auto-install this url as well
$DP_CONFIG['SETTINGS']['core.deskpro_url'] = dev_config_read_file('LOCALHOST_URL.txt');

require __DIR__.'/paths_config.php';
require __DIR__.'/mail_config.php';
require __DIR__.'/logs_config.php';
require __DIR__.'/misc_config.php';
require __DIR__.'/settings_override.php';

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

define('DP_SET_MIN_MEMSIZE', 94371840);
define('DP_SET_MEMSIZE_ALWAYS', true);
ini_set('memory_limit', -1);
set_time_limit(0);

########################################################################################################################

