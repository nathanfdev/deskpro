<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

abstract class AbstractImprovedBuild extends AbstractBuild
{
    /**
     * @var string
     */
    public static $title = '';

    /**
     * True to indicate that this build didnt modify any of the assets
     * usually refreshed by PostBuild.
     *
     * @var bool
     */
    public static $skipPostBuild = false;

    /**
     * Run new table queries.
     * These queries ar erun while the helpdesk is running.
     */
    public function addNewTables()
    {
    }

    /**
     * Run table alters that are backwards compatible (e.g. adding new fields).
     * These alters will be run while the helpdesk is running.
     */
    public function runBcAlters()
    {
    }

    /**
     * Run table alters that are NOT backwards compatible.
     */
    public function runAlters()
    {
    }

    /**
     * Run arbitrary code that is backwards compatible.
     */
    public function runBc()
    {
    }

    /**
     * Run arbitrary code (considered not backwards compatible).
     */
    public function run()
    {
    }
}
