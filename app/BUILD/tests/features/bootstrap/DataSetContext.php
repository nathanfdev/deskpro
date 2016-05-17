<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DpBehat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DpTestSrc\TestBundle\DataSetManager;

class DataSetContext extends BaseContext
{
    /**
     * @var DataSetContext
     */
    private static $instance;

    /**
     * @var DataSetManager
     */
    private $dataset_manager;

    private static $lastInstalledDataSet = null;

    private static $reinstall = false;

    /**
     * @var bool just used to test this context in tests.feature
     */
    private $ran_install = false;

    /**
     * @var array
     */
    private $reset_contexts = [];

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment          = $scope->getEnvironment();
        $this->reset_contexts = $environment->getContexts();
    }

    public function __construct(DataSetManager $dataset_manager)
    {
        $this->dataset_manager = $dataset_manager;
        self::$instance        = $this;
    }

    /**
     * @BeforeFeature
     */
    public static function reinstall()
    {
        self::$reinstall = true;
    }

    /**
     * @When I install the :set data set
     */
    public function iInstallDataSet($set)
    {
        if ((self::$lastInstalledDataSet === $set) && !self::$reinstall) {
            echo 'data set already loaded';

            foreach ($this->reset_contexts as $ctx) {
                if ($ctx instanceof RebootableContextInterface) {
                    $ctx->rebootContext();
                }
            }

            return; // this scenario was not tagged as @reinstall, exiting
        }
        $install_start = time();
        $this->dataset_manager->install($set);
        self::$lastInstalledDataSet = $set;
        $this->ran_install          = true;

        $this->resetAllContext();
        foreach ($this->reset_contexts as $ctx) {
            if ($ctx instanceof RebootableContextInterface) {
                $ctx->rebootContext();
            }
        }

        if (self::$reinstall) {
            echo 'successfully reinstalled data set (took '.(time() - $install_start).' seconds)';
        } else {
            echo 'successfully installed data set (took '.(time() - $install_start).' seconds)';
        }

        self::$reinstall = false;
    }

    /**
     * @Given I have access to dataset_manager
     */
    public function iHaveAccessToDbsetManager()
    {
        expect($this->dataset_manager)->toNotBeNull();
    }

    /**
     * @Given I have already installed the :set data set
     */
    public function iHaveAlreadyInstalledTheFreshDataSet($set)
    {
        expect(self::$lastInstalledDataSet)->toBe($set);
    }

    /**
     * @Given I have not tagged this scenario with :tag
     */
    public function iHaveNotTaggedThisScenarioWith($tag)
    {
        // you better not have!
    }

    /**
     * @When I attempt to reinstall the :set data set
     */
    public function iAttemptToReinstallTheFreshDataSet($set)
    {
        $this->iInstallDataSet($set);
    }

    /**
     * @Then the database will not be installed
     */
    public function theDatabaseWillNotBeInstalled()
    {
        expect($this->ran_install)->toBe(false);
    }

    /**
     * @Given I have tagged this scenario with :tag
     */
    public function iHaveTaggedThisScenarioWith($tag)
    {
        // ok, I trust you...
    }

    /**
     * @Then the database will be installed/reinstalled
     */
    public function theDatabaseWillBeTnstalled()
    {
        expect($this->ran_install)->toBe(true);
    }
}
