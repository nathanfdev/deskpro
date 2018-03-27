<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * Add this interface to builds where it is okay to skip the PostBuild sync scripts.
 * This is safe to do when there have been NO changes to any of the things PostBuild syncs:.
 *
 * - Language manifest or lang.php files
 * - Apps
 * - Templates
 * - DefaultData (e.g. cron jobs, filters, etc)
 * - Portal CSS
 *
 * The dpdev:gen:migration-script command will try to detect this for you, but ultimately
 * needs a human to review this to make sure.
 */
interface SkipPostBuildInterface
{
}
