<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\JobQueue\Processor\UsersourceSyncProcessor;
use Application\DeskPRO\Usersource\Sync\SyncException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use League\Url\Url;
use Orb\Auth\Adapter\CallbackInterface;
use Orb\Auth\Adapter\ExtraDetailsInterface;
use Orb\Auth\Adapter\IframeSsoInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;

/**
 * @ApiModes("all")
 */
class UsersourcesController extends AbstractController
{
    public function personRefreshAction($usersource_id, $identity_or_email)
    {
        // find Usersource
        $sources = $this->getUsersourceManager()->getAll()->mustBeEnabled()->mustHaveId($usersource_id);
        if (!$source = $sources->getFirstOrNull()) {
            throw $this->createNotFoundException('usersource id='.$usersource_id.' not found or not enabled');
        }

        /** @var \Application\DeskPRO\Usersource\Sync\SyncManager $sync_manager */
        $sync_manager = $this->container->getSystemService('usersource_sync_manager');

        // run sync algorithm for this usersource with the passed identifier/email
        try {
            if ($sync_manager->refreshIdentity($source, $identity_or_email)) {
                return $this->createApiSuccessResponse();
            } else {
                return $this->createApiErrorResponse('sync_error', 'unable to sync');
            }
        } catch (SyncException $e) {
            return $this->createApiErrorResponse('sync_error', 'a problem occurred when trying to sync');
        }
    }

    public function startUsersourceSyncAction()
    {
        // find any non complete job, may way to expand this to error'ed jobs in the future
        // depending on how we use this endpoint
        $job = $this->em
            ->createQuery('SELECT j FROM DeskPRO:Job j WHERE j.type = :jtype AND j.status != :jstatus')
            ->setParameter('jtype', UsersourceSyncProcessor::JOB_TYPE)
            ->setParameter('jstatus', Job::STATUS_COMPLETE)
            ->getOneOrNullResult()
        ;

        if (!$job) {
            // only create a new sync job if there is not already a sync job
            // the sync job renews itself continually, so we can't allow two going
            // at once
            $this->container->getJobQueue()->addJob(new Job(
                'usersource_sync'
            ));

            return $this->createApiSuccessResponse();
        }

        return $this->createApiErrorResponse('sync_in_progress', 'Cannot start sync because it is already running in the job queue');
    }

    public function listByTypeAction($type)
    {
        if ($type == Usersource::TYPE_USER) {
            $sources = $this->getUsersourceManager()->getAll()->configuredForUsers(true);
        } else {
            $sources = $this->getUsersourceManager()->getAll()->configuredForAgents(true);
        }

        $usersources = array_map(
            function (Usersource $us) {
                return ['usersource' => $us->toApiData(), 'app' => $us->app ? $us->app->toApiData() : null];
            },
            (array) $sources
        );

        return $this->createApiResponse(['usersources' => $usersources]);
    }

    public function availableAppPackagesAction($interface)
    {
        $sources  = $this->getUsersourceManager()->getAll()->forInterface($interface, true);
        $packages = $this->container->getAppManager()->getAllPackages();

        $available_packages = array_filter($packages, function (AppPackage $package) use ($sources, $interface) {
            if ($package->isUsersource()) {
                if ($interface !== 'agent' && $package->isAgentOnlyUsersource()) {
                    return false;
                }

                    /** @var \Application\DeskPRO\Entity\Usersource $source */
                    foreach ($sources as $source) {
                        /** @var \Application\DeskPRO\Entity\AppInstance $app */
                        if ($app = $source->app) {
                            if ($app->package->name === $package->name && $package->is_single) {
                                return false;
                            }
                        }
                    }

                return true;
            }

            return false;
        }
        );

        $available_packages = array_map(
            function (AppPackage $package) {
                return $package->toApiData();
            },
            $available_packages
        );

        return $this->createApiResponse($available_packages);
    }

    public function getUsersourceAction($type, $id)
    {
        if ($id === 'deskpro') {
            $sources = $this->getUsersourceManager()->getAll()->withNoApp();
        } else {
            $sources = $this->getUsersourceManager()->getAll()->mustHaveId($id);
        }

        if ($type === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = $sources->getFirstOrNull();

        if (!$source) {
            throw $this->createNotFoundException('usersource id='.$id.' not found for type='.$type);
        }

        return $this->createApiResponse(
            [
                'usersource' => $source->toApiData(),
                'app'        => $source->app ? $source->app->toApiData() : null,
            ]
        );
    }

    public function syncStartAction()
    {
        $this->getSyncManager()->clearStopSignal();
        $this->getSyncManager()->rescheduleSync(new \DateTime());

        return $this->createApiSuccessResponse();
    }

    public function syncStopAction()
    {
        $this->getSyncManager()->abortSyncJobs();
        $this->getSyncManager()->signalJobToStop();

        return $this->createApiSuccessResponse();
    }

    public function syncStatusAction()
    {
        $next = $this->getSyncManager()->getNextScheduledSyncDate();

        return $this->createApiResponse(
            [
                'running_now' => $this->getSyncManager()->isSyncRunning(),
                'next_sync'   => $next ? $next->format('Y-m-d H:i:s') : null,
            ]
        );
    }

    public function getSyncInformationAction($app_id)
    {
        $source = $this->getUsersourceManager()->getAll()->withAppId($app_id)->getFirstOrNull();

        if (!$source) {
            throw $this->createNotFoundException('usersource app id='.$app_id);
        }

        $sync_log = null;
        if ($most_recent_log = $this->getSyncManager()->getMostRecentLog($source)) {
            $sync_log = $most_recent_log->toApiData();

            // phase 1 time
            // no end date means phase 1 is running
            if (null === $most_recent_log->getDateEnd()) {
                $sync_log['phase_1_running'] = true;
            } else {
                $sync_log['phase_1_running'] = false;
            }

            // phase 2 time
            $time_two = $most_recent_log->getPhaseTwoTimeInSeconds();
            // no end date means phase 2 _might_ be running
            $sync_log['phase_2_running'] = false;
            if (null === $most_recent_log->getDateEnd()) {
                $sync_log['phase_2_running'] = true;
                $sync_log['phase_2_show']    = false;
            } elseif ($time_two > 2) { // don't show phase 2 unless it took at least a few seconds
                $sync_log['phase_2_show'] = true;
            } else {
                $sync_log['phase_2_show'] = false; // else signal that we shouldn't show it
            }
        }

        return $this->createApiResponse(
            [
                'sync_log' => $sync_log,
            ]
        );
    }

    /**
     * @return \Application\DeskPRO\Usersource\Sync\SyncManager
     */
    protected function getSyncManager()
    {
        return $this->container->getSystemService('usersource_sync_manager');
    }

    public function getUsersourceExtraAction($type, $app_id)
    {
        $sources = $this->getUsersourceManager()->getAll();

        if ($type === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = $sources->withAppId($app_id)->getFirstOrNull();

        if (!$source) {
            throw $this->createNotFoundException('usersource app id='.$app_id.' not found for interface='.$type);
        }

        $adapter = $this->container->getSystemService('usersource_auth_adapter_factory')->getAuthAdapter($source, null, $type);

        $details = [];
        if ($adapter instanceof ExtraDetailsInterface) {
            try {
                $details = $adapter->getExtraDetails();
            } catch (\Exception $e) {
            }
        }

        return $this->createApiResponse(
            [
                'usersource_details' => $details,
            ]
        );
    }

    public function postUsersourceAction($type, $id)
    {
        $source = $this->findUsersourceOfType($id, $type);

        if (!$source) {
            throw $this->createNotFoundException('usersource id='.$id.' not found for type='.$type);
        }

        $source->setTitle($this->in->getString('title'));
        $source->setIsEnabled($this->in->getBool('is_enabled'));
        $source->setOptions($this->in->getCleanValueArray('options'));
        $this->em->persist($source);
        $this->em->flush();

        $this->em->getRepository('DeskPRO:Usersource')->checkAndEnableDeskpro();

        return $this->createApiResponse(
            [
                'usersource' => $source->toApiData(),
                'app'        => $source->app ? $source->app->toApiData() : null,
            ]
        );
    }

    public function getIframeAction($app_id, $interface)
    {
        $sources = $this->getUsersourceManager()->getAll();

        if ($interface === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = null;
        foreach ($sources as $usersource) {
            if ($usersource->app && $usersource->app->id == $app_id) {
                $source = $usersource;
                break;
            }
        }

        if (!$source) {
            return $this->createApiErrorResponse(

                'not found',
                'could not find usersource for "'.$interface.'" interface with app id "'.$app_id.'"'
            );
        }

        /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $factory */
        $factory = $this->container->getSystemService('usersource_auth_adapter_factory');
        $adapter = $factory->getAuthAdapter($source, SsoLoginActionInterface::CONTEXT_BACKGROUND, $interface);

        if (!$adapter instanceof IframeSsoInterface) {
            return $this->createApiErrorResponse(

                'invalid adapter',
                'this adapter doesn\'t support iframe SSO'
            );
        }

        if ($adapter instanceof CallbackInterface) {
            // append noredirect so that the callback url knows not to refresh the page on success
            $url                      = Url::createFromUrl($adapter->getCallbackUrl());
            $query                    = $url->getQuery();
            $query['usersource_test'] = true;
            $adapter->setCallbackUrl((string) $url);
        }

        $vars = array_merge(
            [
                'iframe_url' => '',
                'render'     => true,
            ],
            $adapter->getIframeTemplateParams(false)
        );

        return $this->createApiSuccessResponse(
            array_merge([
                'iframe_html' => $this->renderView(
                        'DeskPRO:Auth:_sso_iframe_for_test.html.twig',
                        $vars
                    ),
            ],
            $adapter->getIframeTemplateParams(false))
        );
    }

    /**
     * @param string $id
     *
     * @return Response
     */
    public function checkRequirementsAction($id)
    {
        return $this->createApiSuccessResponse(['id' => $id]);
    }

    public function updateDisplayOrderAction()
    {
        $inputOrders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
        $this->em->getRepository('DeskPRO:Usersource')->updateDisplayOrders($inputOrders);

        return $this->createApiSuccessResponse();
    }

    /**
     * @return \Application\DeskPRO\Usersource\UsersourceManager
     */
    protected function getUsersourceManager()
    {
        return $this->container->getSystemService('usersource_manager');
    }

    /**
     * @param $id
     * @param $type
     *
     * @return Usersource|null
     */
    protected function findUsersourceOfType($id, $type)
    {
        if ($id === 'deskpro') {
            $sources = $this->getUsersourceManager()->getAll()->withNoApp();
        } else {
            $sources = $this->getUsersourceManager()->getAll()->mustHaveId($id);
        }

        if ($type === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = $sources->getFirstOrNull();

        return $source;
    }

    /**
     * @param $email
     *
     * @return Person
     */
    protected function createPerson($email)
    {
        $person = Person::newContactPerson(['email' => $email]);
        $this->em->persist($person);

        return $person;
    }
}
