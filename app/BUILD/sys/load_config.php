<?php

/**
 * @deprecated
 *
 * @return string
 */
function dp_get_log_dir()
{
    /* @var \DpRun\DpEnv $DP_ENV */
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
    /* @var \DpRun\DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getUserBackupsDir();
}

/**
 * @deprecated inject @=service('deskpro.app_env').getUserTmpDir() instead
 *
 * @return string
 */
function dp_get_tmp_dir()
{
    /* @var \DpRun\DpEnv $DP_ENV */
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
    /* @var \DpRun\DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getConfig('paths.php_path');
}

/**
 * @deprecated Use deskpro.app_env service getConsolePhpCommand()
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
    /* @var \DpRun\DpEnv $DP_ENV */
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
    /* @var \DpRun\DpEnv $DP_ENV */
    global $DP_ENV;

    return $DP_ENV->getConfig('paths.mysql_path');
}
