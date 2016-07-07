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

namespace DeskPRO\Bundle\UpdateBundle\Session;

use DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use DeskPRO\Bundle\UpdateBundle\Session\SessionStep\SessionStep;
use DeskPRO\Bundle\UpdateBundle\Session\SessionStep\StatusStep;
use DeskPRO\Component\Util\DebugUtils;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionLogHandler extends AbstractHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * SessionLogHandler constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct(Logger::DEBUG, true);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (empty($record['context']['keyEvent'])) {
            return false;
        }

        $this->handleKeyEvent($record, $record['context']['keyEvent']);

        return false === $this->bubble;
    }

    /**
     * @param array       $record
     * @param LogKeyEvent $keyEvent
     */
    private function handleKeyEvent(array $record, LogKeyEvent $keyEvent)
    {
        if (!$this->container->has('dp.updater.session_manager_factory')) {
            return;
        }

        $smf = $this->container->get('dp.updater.session_manager_factory');
        if (!$smf->isEnabled()) {
            return;
        }

        $manager = $smf->getManager();

        $eventId = $keyEvent->getId();
        $parts   = explode('.', $eventId);
        $eventNs = array_shift($parts);

        switch ($eventNs) {
            case 'AutoUpgrade':
                $manager->mutateSession(function (UpdateSession $session) use ($keyEvent) {
                    return $this->handleAutoUpgradeEvent($session, $keyEvent);
                });
                break;

            case 'StatusCheck':
            case 'DistroManifestLoader':
                $manager->mutateSession(function (UpdateSession $session) use ($keyEvent) {
                    $step = $session->getStatusStep();

                    return $this->handleStatusCheckEvent($step, $keyEvent);
                });
                break;

            case 'DbBackup':
                $manager->mutateSession(function (UpdateSession $session) use ($keyEvent) {
                    $step = $session->getStep(UpdateSession::STEP_BACKUP);

                    return $this->handleDbBackupEvent($step, $keyEvent);
                });
                break;

            case 'DistroDownload':
                $manager->mutateSession(function (UpdateSession $session) use ($keyEvent) {
                    $step = $session->getStep(UpdateSession::STEP_DOWNLOAD_DISTRO);

                    return $this->handleDistroDownloadEvent($step, $keyEvent);
                });
                break;

            case 'DistroInstaller':
                $manager->mutateSession(function (UpdateSession $session) use ($keyEvent) {
                    $step = $session->getStep(UpdateSession::STEP_EXTRACT_DISTRO);

                    return $this->handleDistroInstallEvent($step, $keyEvent);
                });
                break;

            case 'BuildActivator':
                $manager->mutateSession(function (UpdateSession $session) use ($keyEvent) {
                    return $this->handleBuildActivateEvent($session, $keyEvent);
                });
                break;
        }
    }

    /**
     * @param UpdateSession $session
     * @param LogKeyEvent   $keyEvent
     *
     * @return bool
     */
    private function handleAutoUpgradeEvent(UpdateSession $session, LogKeyEvent $keyEvent)
    {
        switch ($keyEvent->getId()) {
            case 'AutoUpgrade.start':
                $session->start();
                break;

            case 'AutoUpgrade.success':
                $session->finished('Upgrade process is complete.');
                break;

            case 'AutoUpgrade.error':
                if ($keyEvent->has('exception')) {
                    /** @var \Exception $e */
                    $e = $keyEvent->get('exception');
                    $session->finishedWithError('An unexpected error occurred', 'An exception was raised: '.DebugUtils::getExceptionSummary($e));
                } else {
                    $session->finishedWithError('An upgrade process returned with an error status');
                }
                break;

            default:
                return false;
        }

        return true;
    }

    /**
     * @param StatusStep  $step
     * @param LogKeyEvent $keyEvent
     *
     * @return bool
     */
    private function handleStatusCheckEvent(StatusStep $step, LogKeyEvent $keyEvent)
    {
        switch ($keyEvent->getId()) {
            case 'StatusCheck.start':
                $step->start();
                break;

            case 'StatusCheck.updates_available':
                $step->finished('DeskPRO needs to be updated');
                $step->setRequiresUpdate();
                break;

            case 'StatusCheck.no_updates_available':
                $step->finished('DeskPRO does not need to be updated');
                break;

            case 'StatusCheck.error':
                if ($keyEvent->has('exception')) {
                    /** @var \Exception $e */
                    $e = $keyEvent->get('exception');

                    $step->finishedWithError('We could not connect with the DeskPRO version server', $keyEvent->get('message', DebugUtils::getExceptionSummary($e)));
                } else {
                    $step->finishedWithError('We could not connect with the DeskPRO version server', $keyEvent->get('message', ''));
                }
                break;

            default:
                return false;
        }

        return true;
    }

    /**
     * @param SessionStep $step
     * @param LogKeyEvent $keyEvent
     *
     * @return bool
     */
    private function handleDbBackupEvent(SessionStep $step, LogKeyEvent $keyEvent)
    {
        switch ($keyEvent->getId()) {
            case 'DbBackup.start':
                $step->start();
                break;

            case 'DbBackup.success':
                $step->finished('Database backup completed successfully');
                break;

            case 'StatusCheck.error':
                if ($keyEvent->has('exception')) {
                    /** @var \Exception $e */
                    $e = $keyEvent->get('exception');

                    $step->finishedWithError('Failed to make database backup', $keyEvent->get('message', DebugUtils::getExceptionSummary($e)));
                } else {
                    $step->finishedWithError('Failed to make database backup', $keyEvent->get('message', ''));
                }
                break;

            default:
                return false;
        }

        return true;
    }

    /**
     * @param SessionStep $step
     * @param LogKeyEvent $keyEvent
     *
     * @return bool
     */
    private function handleDistroDownloadEvent(SessionStep $step, LogKeyEvent $keyEvent)
    {
        switch ($keyEvent->getId()) {
            case 'DistroDownload.start':
                $step->start();
                break;

            case 'DistroDownload.success':
                $step->finished('Downloaded DeskPRO distribution successfully');
                break;

            case 'DistroDownload.error':
                if ($keyEvent->has('exception')) {
                    /** @var \Exception $e */
                    $e = $keyEvent->get('exception');

                    $step->finishedWithError('Failed to download DeskPRO distribution', $keyEvent->get('message', DebugUtils::getExceptionSummary($e)));
                } else {
                    $step->finishedWithError('Failed to download DeskPRO distribution', $keyEvent->get('message', ''));
                }
                break;

            default:
                return false;
        }

        return true;
    }

    /**
     * @param SessionStep $step
     * @param LogKeyEvent $keyEvent
     *
     * @return bool
     */
    private function handleDistroInstallEvent(SessionStep $step, LogKeyEvent $keyEvent)
    {
        switch ($keyEvent->getId()) {
            case 'DistroInstaller.start':
                $step->start();
                break;

            case 'DistroInstaller.success':
                $step->finished('Downloaded DeskPRO distribution successfully');
                break;

            case 'DistroInstaller.reqCheck.error':
                $problems = $keyEvent->get('problems');
                $step->finishedWithError(
                    'Failed to download DeskPRO distribution',
                    "We discovered the following problems:\n- ".implode("\n - ", $problems)
                );
                break;

            case 'DistroInstaller.error':
                if ($keyEvent->has('exception')) {
                    /** @var \Exception $e */
                    $e = $keyEvent->get('exception');

                    $step->finishedWithError('Failed to download DeskPRO distribution', $keyEvent->get('message', DebugUtils::getExceptionSummary($e)));
                } else {
                    $step->finishedWithError('Failed to download DeskPRO distribution', $keyEvent->get('message', ''));
                }
                break;

            default:
                return false;
        }

        return true;
    }

    /**
     * @param UpdateSession $session
     * @param LogKeyEvent   $keyEvent
     *
     * @return bool
     */
    private function handleBuildActivateEvent(UpdateSession $session, LogKeyEvent $keyEvent)
    {
        $reqCheckStep = $session->getStep(UpdateSession::STEP_REQ_CHECK);
        $hdOffStep    = $session->getStep(UpdateSession::STEP_DISABLE_SITE);
        $hdOnStep     = $session->getStep(UpdateSession::STEP_ENABLE_SITE);
        $updateStep   = $session->getStep(UpdateSession::STEP_UPGRADE);

        switch ($keyEvent->getId()) {
            case 'BuildActivator.reqCheck.success':
                $reqCheckStep->finished('Requirements OK');
                break;

            case 'BuildActivator.reqCheck.error':
                $e = $keyEvent->get('exception');
                if ($e && $e instanceof ReqCheckException) {
                    $desc = [];
                    foreach ($e->getFailedRequirements() as $info) {
                        $desc[] = "- {$info['description']}\n\n{$info['help']}\n\n\n";
                    }

                    $reqCheckStep->finishedWithError(
                        'The new build has updated requirements that your server does not meet',
                        trim(implode('', $desc))
                    );
                } else {
                    $reqCheckStep->finishedWithError(
                        'The new build has updated requirements that your server does not meet',
                        'An unknonw problem occurred while checking server requirements'
                    );
                }
                break;

            case 'BuildActivator.helpdeskState.off.success':
                $hdOffStep->finished('Turned helpdesk off');
                break;

            case 'BuildActivator.helpdeskState.off.error':
                $hdOffStep->finishedWithError('Failed to disable helpdesk');
                break;

            case 'BuildActivator.upgradeRunner.start':
                $updateStep->start();
                break;

            case 'BuildActivator.upgradeRunner.success':
                $updateStep->finished('Upgrade finished');
                break;

            case 'BuildActivator.upgradeRunner.error':
                $updateStep->finishedWithError('Upgrade failed');
                break;

            case 'BuildActivator.runActivator.start':
                $hdOnStep->start();
                break;

            case 'BuildActivator.helpdeskState.enable.success':
                $hdOnStep->finished('Re-enabled helpdesk');
                break;

            case 'BuildActivator.helpdeskState.enable.error':
                if ($keyEvent->has('exception')) {
                    /** @var \Exception $e */
                    $e = $keyEvent->get('exception');

                    $hdOnStep->finishedWithError('Failed to activate the new build', $keyEvent->get('message', DebugUtils::getExceptionSummary($e)));
                } else {
                    $hdOnStep->finishedWithError('Failed to activate the new build', $keyEvent->get('message', ''));
                }
                break;

            case 'BuildActivator.runActivator.warning':
                break;

            default:
                return false;
        }

        return true;
    }
}
