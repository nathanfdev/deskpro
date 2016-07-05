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

namespace DeskPRO\Bundle\UpgradeBundle\BuildActivate;

use DeskPRO\Bundle\UpgradeBundle\BuildActivate\HelpdeskState\HelpdeskStateModifierInterface;
use DeskPRO\Bundle\UpgradeBundle\BuildActivate\ReqCheck\ReqCheckInterface;
use DeskPRO\Bundle\UpgradeBundle\BuildActivate\RunActivator\RunActivatorInterface;
use DeskPRO\Bundle\UpgradeBundle\BuildActivate\UpgradeRunner\UpgradeRunnerInterface;
use DeskPRO\Bundle\UpgradeBundle\Instance\BuildInstance;
use DeskPRO\Component\Util\Timer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This wraps up logic to do with activating an installed build:.
 *
 * - It checks requirements
 * - It runs the upgrade script
 * - It switches the active build trigger file
 * - It will install the run/ directory
 */
class BuildActivator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var HelpdeskStateModifierInterface
     */
    private $helpdeskState;

    /**
     * @var ReqCheckInterface
     */
    private $reqChecker;

    /**
     * @var UpgradeRunnerInterface
     */
    private $upgradeRunner;

    /**
     * @var RunActivatorInterface
     */
    private $runActivator;

    /**
     * BuildActivator constructor.
     *
     * @param HelpdeskStateModifierInterface $helpdeskState
     * @param ReqCheckInterface              $reqChecker
     * @param UpgradeRunnerInterface         $upgradeRunner
     * @param RunActivatorInterface          $runActivator
     * @param LoggerInterface|null           $logger
     */
    public function __construct(HelpdeskStateModifierInterface $helpdeskState, ReqCheckInterface $reqChecker, UpgradeRunnerInterface $upgradeRunner, RunActivatorInterface $runActivator, LoggerInterface $logger = null)
    {
        $this->helpdeskState = $helpdeskState;
        $this->reqChecker    = $reqChecker;
        $this->upgradeRunner = $upgradeRunner;
        $this->runActivator  = $runActivator;

        $this->setLogger($logger ?: new NullLogger());
    }

    /**
     * @param BuildInstance $build
     */
    public function activateBuild(BuildInstance $build)
    {
        $t = Timer::start();

        $this->logger->info('Activating build '.$build->getBuildId());
        $this->logger->debug('appPath: '.$build->getAppPath());
        $this->logger->debug('webPath: '.$build->getWebPath());
        $this->logger->debug('kernelCachePath: '.$build->getKernelCachePath());

        #----------------------------------------
        # Requirement check
        #----------------------------------------

        $t->tick();
        $this->logger->debug('[reqCheck] type: '.get_class($this->reqChecker));

        try {
            $this->reqChecker->assertValidRequirements($build);
            $this->logger->info('[reqCheck] finished ok');
        } catch (\Exception $e) {
            $this->logger->error('[reqCheck] finished with error: '.$e->getMessage());
            // TODO
        } finally {
            $t->tick();
            $this->logger->debug('[reqCheck] took '.$t->formatTime());
        }

        #----------------------------------------
        # Turn helpdesk off
        #----------------------------------------

        $this->logger->debug('[helpdeskState] type: '.get_class($this->helpdeskState));

        try {
            $this->helpdeskState->disableHelpdeskForUpdate();
            $this->logger->info('[helpdeskState] finished ok');
        } catch (\Exception $e) {
            $this->logger->error('[helpdeskState] finished with error: '.$e->getMessage());
            // TODO
        }

        #----------------------------------------
        # Run upgrader
        #----------------------------------------

        $this->logger->debug('[upgradeRunner] type: '.get_class($this->upgradeRunner));
        $t->tick();

        try {
            $this->upgradeRunner->runUpgrade($build);
            $this->logger->info('[upgradeRunner] finished ok');
        } catch (\Exception $e) {
            $this->logger->critical('[upgradeRunner] finished with error: '.$e->getMessage());
            // TODO
        } finally {
            $t->tick();
            $this->logger->debug('[upgradeRunner] took '.$t->formatTime());
        }

        #----------------------------------------
        # Activate run
        #----------------------------------------

        $this->logger->debug('[runActivator] type: '.get_class($this->runActivator));
        $t->tick();

        try {
            $this->runActivator->activateRunDir($build);
            $this->logger->warning('[runActivator] finished ok');
        } catch (\Exception $e) {
            $this->logger->warning('[runActivator] finished with error: '.$e->getMessage());
            // TODO
        } finally {
            $t->tick();
            $this->logger->debug('[runActivator] took '.$t->formatTime());
        }

        #----------------------------------------
        # Turn helpdesk off
        #----------------------------------------

        try {
            $this->helpdeskState->enableHelpdeskFromUpdate();
            $this->helpdeskState->setHelpdeskBuild($build);
            $this->logger->info('[helpdeskState] re-enabled and set build ok');
        } catch (\Exception $e) {
            $this->logger->error('[helpdeskState] failed to re-enable/set build with error: '.$e->getMessage());
            // TODO
        }

        #----------------------------------------
        # Done all
        #----------------------------------------

        $this->logger->info('BuildActivator done all in '.$t->formatTotalTime());
    }
}
