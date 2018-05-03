<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * An ONLINE build means all of these upgrade steps can be run while the helpdesk is still online. That means
 * only backwards compatible changes are allowed.
 *
 * If you need to run code that is not backwards compatible, you should implement the BlockingBuildInterface instead.
 */
interface OnlineBuildInterface
{
    /**
     * Create new tables.
     */
    public function addNewTables();

    /**
     * Run alters that are backwards compatible with the previous version.
     */
    public function runAlters();

    /**
     * Run arbitrary code (e.g. data mutations) that are backwards compatible with the previous version.
     */
    public function run();
}
