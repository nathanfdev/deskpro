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

namespace DeskPRO\Bundle\AppBundle\AppEnv;

interface AppEnvInterface
{
    /**
     * Gets the root DeskPRO directory. This is typically the parent of all other dirs.
     *
     * Example: /path/to/deskpro
     *
     * @return string
     */
    public function getDpRoot();

    /**
     * Gets the www directory.
     *
     * Example: /path/to/deskpro/www
     *
     * @return string
     */
    public function getWwwDir();

    /**
     * Gets the 'name' of the currently active build. The 'name' in this case
     * just means the directory name of the active build.
     *
     * This will depend on the current environment/instance.
     *
     * Example: 502
     *
     * @return string
     */
    public function getAppName();

    /**
     * Gets the path to the current builds app dir.
     *
     * This will depend on the current environment/instance.
     *
     * Example: /path/to/deskpro/app/502
     *
     * @return string
     */
    public function getAppDir();

    /**
     * Gets the path to the current builds kernel cache dir.
     *
     * The kernels decide what the inner structure looks like. Typically
     * each environment and kernel has it's own dir.
     *
     * Example: /path/to/deskpro/var/appcache/502
     *
     * @return string
     */
    public function getAppBaseKernelCacheDir();

    /**
     * Gets the the path to tmp directory.
     * Note that this tmp directory is shared by all builds.
     *
     * Example: /path/to/deskpro/var/tmp
     *
     * @return string
     */
    public function getUserTmpDir();

    /**
     * Gets the path to the cache directory.
     * Note that this directory is shared by all builds.
     *
     * Example: /path/to/deskpro/var/cache
     *
     * @return string
     */
    public function getUserCacheDir();

    /**
     * Gets the path to the logs directory.
     *
     * Example: /path/to/deskpro/var/logs
     *
     * @return string
     */
    public function getUserLogsDir();

    /**
     * Gets the path to the debug directory.
     *
     * Example: /path/to/deskpro/var/debug
     *
     * @return string
     */
    public function getUserDebugDir();

    /**
     * Gets the path to the backups dir.
     *
     * Example: /path/to/deskpro/backups
     *
     * @return string
     */
    public function getUserBackupsDir();

    /**
     * Gets the path to the files dir.
     *
     * Example: /path/to/deskpro/attachments
     *
     * @return string
     */
    public function getUserFilesDir();

    /**
     * prod, dev or test.
     *
     * @return string
     */
    public function getEnvId();

    /**
     * @return bool
     */
    public function isDebug();

    /**
     * @param string $id      The config value you want
     * @param mixed  $default If the value is unset, the default value
     *
     * @return mixed
     */
    public function getConfig($id, $default = null);

    /**
     * Find a config file or null if the file doesnt exist.
     *
     * @param string $f
     *
     * @return null|string
     */
    public function findConfigFile($f);
}
