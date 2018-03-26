<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * A BLOCKING build means the helpdesk must be shut down for this upgrade to apply. This is the only way to apply
 * backwards incompatible changes.
 *
 * If your upgrade is backwards compatible with the previous build, you should use the preferred OnlineBuildInterface.
 */
interface BlockingBuildInterface
{
    /**
     * Create new tables.
     */
    public function addNewTables();

    /**
     * Run table alters.
     */
    public function runAlters();

    /**
     * Run arbitrary code (e.g. data mutations).
     */
    public function run();
}
