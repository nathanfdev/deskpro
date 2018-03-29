<?php

namespace DeskPRO\Bundle\UpdateBundle\Session;

use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\UpdateFailEvent;
use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\UpdateLogEvent;
use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\UpdateStartEvent;
use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\UpdateSuccessEvent;
use DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck\ReqCheckException;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use DeskPRO\Bundle\UpdateBundle\Session\SessionStep\SessionStep;
use DeskPRO\Bundle\UpdateBundle\Session\SessionStep\StatusStep;
use DeskPRO\Component\Util\DebugUtils;
use DpSys\LowError\SystemErrorHandler;
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
        $statService     = $this->container->get('dp.software_service.stats');
        $installUuuid    = $this->container->get('deskpro.app_env')->getInstallUuid();
        $updateSessionId = $this->container->get('dp.updater.session_manager_factory')->getSessionid() ?: 'session';
        $logId           = $installUuuid.'.'.$updateSessionId;

        switch ($keyEvent->getId()) {
            case 'AutoUpgrade.start':
                $session->start();

                $event = UpdateStartEvent::create()
                    ->setUuid($logId)
                    ->setBuild(DP_ACTIVE_BUILD)
                ;
                SystemErrorHandler::tryRun(function () use ($statService, $event) {
                    $statService->sendUpdateStart($event);
                });

                break;

            case 'AutoUpgrade.success':
                $session->finished('Upgrade process is complete.');

                $event = UpdateSuccessEvent::create()
                    ->setUuid($logId)
                    ->setSummary('Complete')
                ;
                SystemErrorHandler::tryRun(function () use ($statService, $event) {
                    $statService->sendUpdateSuccess($event);
                });

                break;

            case 'AutoUpgrade.error':
                if ($keyEvent->has('exception')) {
                    /** @var \Exception $e */
                    $e = $keyEvent->get('exception');
                    $session->finishedWithError('An unexpected error occurred', 'An exception was raised: '.DebugUtils::getExceptionSummary($e));
                } else {
                    $session->finishedWithError('An upgrade process returned with an error status');
                }

                $event = UpdateFailEvent::create()
                    ->setUuid($logId)
                    ->setSummary('Error: '.$session->getSummary())
                ;
                SystemErrorHandler::tryRun(function () use ($statService, $event) {
                    $statService->sendUpdateFail($event);
                });

                break;

            default:
                return false;
        }

        switch ($keyEvent->getId()) {
            case 'AutoUpgrade.success':
            case 'AutoUpgrade.error':
                $logParts = [];

                foreach (['updater.log', 'upgrader.log'] as $logFile) {
                    $p = $this->container->get('deskpro.app_env')->getUserLogsDir().DIRECTORY_SEPARATOR.$logFile;
                    if (file_exists($p)) {
                        $logParts[] = "$logFile\n===================================================\n\n".file_get_contents($p);
                    }
                }

                if ($logParts) {
                    $log   = implode("\n\n\n\n\n", $logParts);
                    $event = UpdateLogEvent::create()
                        ->setUuid($logId)
                        ->setLog($log);
                    SystemErrorHandler::tryRun(function () use ($statService, $event) {
                        $statService->sendUpdateLog($event);
                    });
                }
                break;
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
