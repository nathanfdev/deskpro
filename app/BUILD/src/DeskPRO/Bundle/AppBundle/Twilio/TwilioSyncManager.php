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

namespace DeskPRO\Bundle\AppBundle\Twilio;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twilio\Exceptions\RestException;

/**
 * Class TwilioSyncManager.
 */
class TwilioSyncManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param TwilioAdapter   $twilioAdapter
     * @param RouterInterface $router
     */
    public function __construct(EntityManager $em, TwilioAdapter $twilioAdapter, RouterInterface $router)
    {
        $this->em            = $em;
        $this->twilioAdapter = $twilioAdapter;
        $this->router        = $router;
    }

    /**
     * @param VoiceAccount $account
     *
     * @throws \Exception
     */
    public function syncWorkflow(VoiceAccount $account)
    {
        // reset workflow config to avoid twilio FK errors
        // e.g. attempt to delete a queue or worker but the sid reference is still in the workflow config
        $workflowSid = $account->getQueueWorkflowSid();
        if ($workflowSid) {
            try {
                $this->twilioAdapter->clearWorkflow($account, $this->getAssignmentUrl($account));
            } catch (RestException $e) {
                // unable to connect or bad credentials, skip syncing
                if ($e->getStatusCode() === Response::HTTP_UNAUTHORIZED) {
                    return;
                }

                // unexpected response exception, bubble the exception and stop syncing
                if ($e->getStatusCode() !== Response::HTTP_NOT_FOUND) {
                    throw $e;
                }
            }
        }

        $taskQueueSids = [];
        $workerSids    = [];

        $taskQueueSids[$account->getVoicemailQueueSid()] = true;
        $workerSids[$account->getVoicemailWorkerSid()]   = true;

        // sync voice queues with twilio task queues
        $queues = $account->getQueues();
        foreach ($queues as $queue) {
            $taskQueueSid = $queue->getTaskQueueSid();
            if ($taskQueueSid) {
                try {
                    $this->twilioAdapter->updateTaskQueue($queue);
                } catch (RestException $e) {
                    if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                        // task queue was deleted, re-create it
                        $taskQueue = $this->twilioAdapter->createTaskQueue($queue);
                        $queue->setTaskQueueSid($taskQueue->sid);

                        $this->em->persist($queue);
                        $this->em->flush();
                    }
                }
            } else {
                $taskQueue = $this->twilioAdapter->createTaskQueue($queue);
                $queue->setTaskQueueSid($taskQueue->sid);

                $this->em->persist($queue);
                $this->em->flush();
            }

            $taskQueueSids[$queue->getTaskQueueSid()] = true;
        }

        // sync agents
        // get filters for specific agents
        $agents = $this->em->getRepository(AgentData::class)->findBy([
            'isVoiceEnabled' => true,
        ]);

        foreach ($agents as $agentData) {
            $person         = $agentData->getPerson();
            $activityStatus = TwilioAdapter::getActivityStatus($agentData);

            // set agent worker
            if ($agentData->getVoiceWorkerSid()) {
                try {
                    $this->twilioAdapter->updateAgentWorker($account, $person, $activityStatus);
                } catch (RestException $e) {
                    if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                        $worker = $this->twilioAdapter->createAgentWorker($account, $person, $activityStatus);
                        $agentData->setVoiceWorkerSid($worker->sid);

                        $this->em->persist($agentData);
                        $this->em->flush();
                    }
                }
            } else {
                $worker = $this->twilioAdapter->createAgentWorker($account, $person, $activityStatus);
                $agentData->setVoiceWorkerSid($worker->sid);

                $this->em->persist($agentData);
                $this->em->flush();
            }

            // set agent queue
            if ($agentData->getVoiceTaskQueueSid()) {
                try {
                    $this->twilioAdapter->updateAgentTaskQueue($account, $person);
                } catch (RestException $e) {
                    if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                        $taskQueue = $this->twilioAdapter->createAgentTaskQueue($account, $person);
                        $agentData->setVoiceTaskQueueSid($taskQueue->sid);

                        $this->em->persist($agentData);
                        $this->em->flush();
                    }
                }
            } else {
                $taskQueue = $this->twilioAdapter->createAgentTaskQueue($account, $person);
                $agentData->setVoiceTaskQueueSid($taskQueue->sid);

                $this->em->persist($agentData);
                $this->em->flush();
            }

            $taskQueueSids[$agentData->getVoiceTaskQueueSid()] = true;
            $workerSids[$agentData->getVoiceWorkerSid()]       = true;
        }

        // remove unused task queues and workers
        $taskQueues = $this->twilioAdapter->getTaskQueues($account);
        foreach ($taskQueues as $taskQueue) {
            if (!isset($taskQueueSids[$taskQueue->sid])) {
                $this->twilioAdapter->deleteTaskQueue($account, $taskQueue->sid);
            }
        }

        $workers = $this->twilioAdapter->getWorkers($account);
        foreach ($workers as $worker) {
            if (!isset($workerSids[$worker->sid])) {
                $this->twilioAdapter->deleteWorker($account, $worker->sid);
            }
        }

        // re-configure workflow
        $workflow = $this->twilioAdapter->createOrUpdateWorkflow($account, $this->getAssignmentUrl($account));

        if ($account->getQueueWorkflowSid() !== $workflow->sid) {
            $account->setQueueWorkflowSid($workflow->sid);
            $this->em->persist($account);
            $this->em->flush();
        }
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string
     */
    private function getAssignmentUrl(VoiceAccount $account)
    {
        return $this->router->generate('twilio_assignment_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
