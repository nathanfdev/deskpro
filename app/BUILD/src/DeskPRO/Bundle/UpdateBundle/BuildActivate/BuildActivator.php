<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate;

use DeskPRO\Bundle\UpdateBundle\BuildActivate\HelpdeskState\HelpdeskStateModifierInterface;
use DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckInterface;
use DeskPRO\Bundle\UpdateBundle\BuildActivate\RunActivator\RunActivatorInterface;
use DeskPRO\Bundle\UpdateBundle\BuildActivate\UpgradeRunner\UpgradeRunnerInterface;
use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
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

        $this->logger->info(
            'Activating build '.$build->getBuildId(),
            ['keyEvent' => LogKeyEvent::create('BuildActivator.start')]
        );
        $this->logger->debug('appPath: '.$build->getAppPath());
        $this->logger->debug('webPath: '.$build->getWebPath());
        $this->logger->debug('kernelCachePath: '.$build->getKernelCachePath());

        //----------------------------------------
        // Requirement check
        //----------------------------------------

        $t->tick();
        $this->logger->debug(
            '[reqCheck] type: '.get_class($this->reqChecker),
            ['keyEvent' => LogKeyEvent::create('BuildActivator.reqCheck.start')]
        );

        try {
            $this->reqChecker->assertValidRequirements($build);
            $this->logger->info(
                '[reqCheck] finished ok',
                ['keyEvent' => LogKeyEvent::create('BuildActivator.reqCheck.success')]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                '[reqCheck] finished with error: '.$e->getMessage(),
                ['keyEvent' => LogKeyEvent::createForException('BuildActivator.reqCheck.error', $e)]
            );
            throw $e;
        } finally {
            $t->tick();
            $this->logger->debug('[reqCheck] took '.$t->formatTime());
        }

        //----------------------------------------
        // Turn helpdesk off
        //----------------------------------------

        $this->logger->debug(
            '[helpdeskState] type: '.get_class($this->helpdeskState),
            ['keyEvent' => LogKeyEvent::create('BuildActivator.helpdeskState.off.start')]
        );

        try {
            $this->helpdeskState->disableHelpdeskForUpdate();
            $this->logger->info(
                '[helpdeskState] finished ok',
                ['keyEvent' => LogKeyEvent::create('BuildActivator.helpdeskState.off.success')]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                '[helpdeskState] finished with error: '.$e->getMessage(),
                ['keyEvent' => LogKeyEvent::createForException('BuildActivator.helpdeskState.off.error', $e)]
            );
            throw $e;
        }

        //----------------------------------------
        // Run upgrader
        //----------------------------------------

        $this->logger->debug(
            '[upgradeRunner] type: '.get_class($this->upgradeRunner),
            ['keyEvent' => LogKeyEvent::create('BuildActivator.upgradeRunner.start')]
        );
        $t->tick();

        try {
            $this->upgradeRunner->runUpgrade($build);
            $this->logger->info(
                '[upgradeRunner] finished ok',
                ['keyEvent' => LogKeyEvent::create('BuildActivator.upgradeRunner.success')]
            );
        } catch (\Exception $e) {
            $this->logger->critical(
                '[upgradeRunner] finished with error: '.$e->getMessage(),
                ['keyEvent' => LogKeyEvent::createForException('BuildActivator.upgradeRunner.error', $e)]
            );

            throw $e;
        } finally {
            $t->tick();
            $this->logger->debug('[upgradeRunner] took '.$t->formatTime());
        }

        //----------------------------------------
        // Activate run
        //----------------------------------------

        $this->logger->debug(
            '[runActivator] type: '.get_class($this->runActivator),
            ['keyEvent' => LogKeyEvent::create('BuildActivator.runActivator.start')]
        );
        $t->tick();

        try {
            $this->runActivator->activateRunDir($build);
            $this->logger->warning(
                '[runActivator] finished ok',
                ['keyEvent' => LogKeyEvent::create('BuildActivator.runActivator.success')]
            );
        } catch (\Exception $e) {
            // No throw, its only a warning
            $this->logger->warning(
                '[runActivator] finished with error: '.$e->getMessage(),
                ['keyEvent' => LogKeyEvent::createForException('BuildActivator.runActivator.warning', $e)]
            );
        } finally {
            $t->tick();
            $this->logger->debug('[runActivator] took '.$t->formatTime());
        }

        //----------------------------------------
        // Turn helpdesk back on
        //----------------------------------------

        $this->logger->debug(
            '[helpdeskState] enable the helpdesk on the new build',
            ['keyEvent' => LogKeyEvent::create('BuildActivator.helpdeskState.enable.start')]
        );

        try {
            $this->helpdeskState->enableHelpdeskFromUpdate();
            $this->helpdeskState->setHelpdeskBuild($build);
            $this->logger->info(
                '[helpdeskState] re-enabled and set build ok',
                ['keyEvent' => LogKeyEvent::create('BuildActivator.helpdeskState.enable.success')]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                '[helpdeskState] failed to re-enable/set build with error: '.$e->getMessage(),
                ['keyEvent' => LogKeyEvent::createForException('BuildActivator.helpdeskState.enable.error', $e)]
            );
            throw $e;
        }

        //----------------------------------------
        // Done all
        //----------------------------------------

        $this->logger->info('BuildActivator done all in '.$t->formatTotalTime());
        $this->logger->info(
            'BuildActivator done all in '.$t->formatTotalTime(),
            ['keyEvent' => LogKeyEvent::create('BuildActivator.success')]
        );
    }
}
