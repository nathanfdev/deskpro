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

/**
 * @deprecated
 *
 * @return string
 */
function dp_get_log_dir()
{
    /* @var \DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getUserLogsDir();
}

/**
 * @deprecated
 *
 * @return string
 */
function dp_get_backup_dir()
{
    /* @var \DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getUserBackupsDir();
}

/**
 * @deprecated
 *
 * @return string
 */
function dp_get_tmp_dir()
{
    /* @var \DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getUserTmpDir();
}

/**
 * @deprecated
 *
 * @return string
 */
function dp_get_php_path()
{
    /* @var \DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getConfig('paths.php_path');
}

/**
 * @deprecated
 *
 * @param string $script
 * @param string $params
 *
 * @return string
 */
function dp_get_php_command($script, $params = '')
{
    $cmd = dp_get_php_path().' '
        .escapeshellarg($script).' '
        .(defined('DP_PHP_BIN_ARGS') ? DP_PHP_BIN_ARGS.' ' : '')
        .$params;

    return $cmd;
}

/**
 * @deprecated
 *
 * @return bool
 */
function dp_is_php_path_guessed()
{
    return false;
}

/**
 * @deprecated
 *
 * @return string
 */
function dp_get_mysqldump_path()
{
    /* @var \DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getConfig('paths.mysqldump_path');
}

/**
 * @deprecated
 *
 * @return string
 */
function dp_get_mysql_path()
{
    /* @var \DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getConfig('paths.mysql_path');
}
