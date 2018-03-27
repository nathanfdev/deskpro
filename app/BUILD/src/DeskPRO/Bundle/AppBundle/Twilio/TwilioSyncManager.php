<?php

namespace DeskPRO\Bundle\AppBundle\Twilio;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param TwilioAdapter            $twilioAdapter
     * @param RouterInterface          $router
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EntityManager $em, TwilioAdapter $twilioAdapter, RouterInterface $router, EventDispatcherInterface $dispatcher)
    {
        $this->em            = $em;
        $this->twilioAdapter = $twilioAdapter;
        $this->router        = $router;
        $this->dispatcher    = $dispatcher;
    }

    /**
     * @param VoiceAccount $account
     *
     * @throws \Exception
     */
    public function syncAccount(VoiceAccount $account)
    {
        // reset workflow config to avoid twilio FK errors
        // e.g. attempt to delete a queue or worker but the sid reference is still in the workflow config
        $workflowSid = $account->getQueueWorkflowSid();
        if ($workflowSid) {
            try {
                $workflow = $this->twilioAdapter->clearWorkflow($account, $this->getAssignmentUrl($account));

                $account->setQueueWorkflowSid($workflow->sid);
                $this->em->persist($account);
                $this->em->flush();
            } catch (RestException $e) {
                // first twilio api call
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

        // fetch existing task queues and workers
        // to check if they were changed and needed to be updated
        $taskQueues = [];
        $workers    = [];

        foreach ($this->twilioAdapter->getTaskQueues($account) as $taskQueue) {
            $taskQueues[$taskQueue->sid] = $taskQueue;
        }
        foreach ($this->twilioAdapter->getWorkers($account) as $worker) {
            $workers[$worker->sid] = $worker;
        }

        $taskQueueSids = [];
        $workerSids    = [];

        $taskQueueSids[$account->getVoicemailQueueSid()] = true;
        $workerSids[$account->getVoicemailWorkerSid()]   = true;

        // sync voice queues with twilio task queues
        $queues = $account->getQueues();
        foreach ($queues as $queue) {
            if ($queue->getTaskQueueSid()) {
                // the voice queue already has a twilio task queue sid, trying to update if something was changed
                try {
                    $this->twilioAdapter->updateTaskQueue(
                        $queue,
                        isset($taskQueues[$queue->getTaskQueueSid()]) ? $taskQueues[$queue->getTaskQueueSid()] : null
                    );
                } catch (RestException $e) {
                    if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                        // task queue was deleted, re-create it
                        $this->createTaskQueue($queue);
                    }
                }
            } else {
                // the voice queue doesn't have a twilio task queue relation yet, creating a new one
                $this->createTaskQueue($queue);
            }

            $taskQueueSids[$queue->getTaskQueueSid()] = true;
        }

        // sync agents
        // get filters for specific agents
        $agents = $this->em->getRepository(AgentData::class)->findBy([
            'isVoiceEnabled' => true,
        ]);

        foreach ($agents as $agentData) {
            $person = $agentData->getPerson();

            // sync agent worker
            if ($agentData->getVoiceWorkerSid()) {
                try {
                    $this->twilioAdapter->updateAgentWorker(
                        $account,
                        $person,
                        TwilioAdapter::getActivityStatus($agentData),
                        isset($workers[$agentData->getVoiceWorkerSid()]) ? $workers[$agentData->getVoiceWorkerSid()] : null
                    );
                } catch (RestException $e) {
                    if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                        $this->createAgentWorker($account, $agentData);
                    }
                }
            } else {
                // the agent doesn't have a worker yet, creating a new one
                $this->createAgentWorker($account, $agentData);
            }

            // sync agent queue
            if ($agentData->getVoiceTaskQueueSid()) {
                try {
                    $this->twilioAdapter->updateAgentTaskQueue(
                        $account,
                        $person,
                        isset($taskQueues[$agentData->getVoiceTaskQueueSid()]) ? $taskQueues[$agentData->getVoiceTaskQueueSid()] : null
                    );
                } catch (RestException $e) {
                    if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                        $this->createAgentTaskQueue($account, $agentData);
                    }
                }
            } else {
                // the agent doesn't have a direct call queue yet, creating a new one
                $this->createAgentTaskQueue($account, $agentData);
            }

            $taskQueueSids[$agentData->getVoiceTaskQueueSid()] = true;
            $workerSids[$agentData->getVoiceWorkerSid()]       = true;
        }

        // check for outdated task queues and workers
        // remove outdated task queues
        foreach ($taskQueues as $taskQueue) {
            if (!isset($taskQueueSids[$taskQueue->sid])) {
                $this->twilioAdapter->deleteTaskQueue($account, $taskQueue->sid);
            }
        }

        // remove outdated workers
        foreach ($workers as $worker) {
            if (!isset($workerSids[$worker->sid])) {
                $this->twilioAdapter->deleteWorker($account, $worker->sid);
            }
        }

        // re-configure the workflow
        $workflow = $this->twilioAdapter->createOrUpdateWorkflow($account, $this->getAssignmentUrl($account));

        if ($account->getQueueWorkflowSid() !== $workflow->sid) {
            $account->setQueueWorkflowSid($workflow->sid);
            $this->em->persist($account);
            $this->em->flush();
        }
    }

    /**
     * @param VoiceQueue $queue
     */
    private function createTaskQueue(VoiceQueue $queue)
    {
        $taskQueue = $this->twilioAdapter->createTaskQueue($queue);
        $queue->setTaskQueueSid($taskQueue->sid);

        $this->em->persist($queue);
        $this->em->flush();
    }

    /**
     * @param VoiceAccount $account
     * @param AgentData    $agentData
     */
    private function createAgentTaskQueue(VoiceAccount $account, AgentData $agentData)
    {
        $taskQueue = $this->twilioAdapter->createAgentTaskQueue($account, $agentData->getPerson());
        $agentData->setVoiceTaskQueueSid($taskQueue->sid);

        $this->em->persist($agentData);
        $this->em->flush();
    }

    /**
     * @param VoiceAccount $account
     * @param AgentData    $agentData
     */
    private function createAgentWorker(VoiceAccount $account, AgentData $agentData)
    {
        $worker = $this->twilioAdapter->createAgentWorker(
            $account,
            $agentData->getPerson(),
            TwilioAdapter::getActivityStatus($agentData)
        );

        $agentData->setVoiceWorkerSid($worker->sid);

        $this->em->persist($agentData);
        $this->em->flush();

        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
            'type'        => 'admin',
            'person_id'   => 0,
            'person_name' => 'System',
            'target'      => $agentData->getPerson()->getId(),
        ]));
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
