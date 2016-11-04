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

namespace DeskPRO\Bundle\AppBundle\Twilio;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioAvailableNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioExistingNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioPaginate;
use Doctrine\ORM\EntityManager;
use Twilio\Exceptions\RestException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance;
use Twilio\Rest\Client;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance;
use Twilio\Values;

/**
 * Class TwilioAdapter.
 */
class TwilioAdapter
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Api\V2010\AccountInstance|false
     */
    public function getAccount(VoiceAccount $account)
    {
        $accountSid = $account->getAccountSid();
        if (!isset($this->cache['account'][$accountSid])) {
            try {
                $value = $this->getClient($account)->getAccount()->fetch();
            } catch (\Exception $e) {
                $value = false;
            }

            $this->cache['account'][$accountSid] = $value;
        }

        return $this->cache['account'][$accountSid];
    }

    /**
     * @param VoiceAccount $account
     * @param string       $countryCode
     * @param string       $type
     * @param array        $options
     *
     * @return TwilioAvailableNumber[]
     */
    public function getAvailablePhoneNumbers(VoiceAccount $account, $countryCode, $type, array $options)
    {
        $numbers = [];

        try {
            $result  = $this->getClient($account)->availablePhoneNumbers($countryCode)->$type->page($options);
            $exclude = $this->getAccountNumbersList($account);

            foreach ($result as $apiNumber) {
                $numbers[] = new TwilioAvailableNumber(
                    $apiNumber,
                    $account,
                    isset($exclude[$apiNumber->phoneNumber]),
                    $type
                );
            }
        } catch (\Exception $e) {
        }

        return $numbers;
    }

    /**
     * @param VoiceAccount $account
     * @param int          $pageNum
     *
     * @return TwilioPaginate
     */
    public function getExistingPhoneNumbers(VoiceAccount $account, $pageNum = 1)
    {
        try {
            $page = $this->getClient($account)->incomingPhoneNumbers->page([], Values::NONE, Values::NONE, $pageNum - 1);

            $exclude = $this->getAccountNumbersList($account);
            $numbers = [];
            foreach ($page as $apiNumber) {
                $numbers[] = new TwilioExistingNumber(
                    $apiNumber,
                    $account,
                    isset($exclude[$apiNumber->phoneNumber])
                );
            }

            return new TwilioPaginate($numbers, $pageNum, $page);
        } catch (\Exception $e) {
            return new TwilioPaginate([], $pageNum);
        }
    }

    /**
     * @param VoiceAccount $account
     * @param array        $data
     *
     * @return bool|IncomingPhoneNumberInstance
     */
    public function buyNumber(VoiceAccount $account, array $data)
    {
        return $this->getClient($account)->incomingPhoneNumbers->create($data);
    }

    /**
     * @param VoiceNumber $number
     * @param array       $options
     *
     * @throws TwilioException
     */
    public function updateNumber(VoiceNumber $number, array $options)
    {
        $account = $number->getAccount();
        if (!$account) {
            throw new TwilioException('Voice number does not have an account reference.');
        }

        $this->getClient($account)->incomingPhoneNumbers($number->getSid())->update($options);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Taskrouter\V1\WorkspaceInstance
     */
    public function createWorkspace(VoiceAccount $account)
    {
        $taskRouter    = $this->getClient($account)->taskrouter;
        $workspaceName = 'DeskPRO Phone';
        $eventFilters  = [
            'task.created',
            'task.canceled',
            'task.deleted',
            'task.updated',
            'task-queue.entered',
            'task-queue.moved',
            'reservation.created',
            'reservation.accepted',
            'reservation.rejected',
            'reservation.timeout',
            'reservation.canceled',
            'reservation.rescinded',
            'reservation.rescinded',
            'workflow.entered',
            'workflow.timeout',
            'worker.activity.update',
        ];

        // ensure we don't have workspace with this name
        foreach ($taskRouter->workspaces->read() as $existingWorkspace) {
            if ($existingWorkspace->friendlyName === $workspaceName) {
                $existingWorkspace->delete();
            }
        }

        // create the workspace
        $workspace = $taskRouter->workspaces->create($workspaceName, [
            'multiTaskEnabled' => 'false',
            'eventsFilter'     => implode(',', $eventFilters),
        ]);

        $workspaceContext   = $taskRouter->workspaces($workspace->sid);
        $requiredActivities = [
            'Offline'      => 'false',
            'Idle'         => 'true',
            'Busy'         => 'false',
            'Reserved'     => 'false',
            'IdleDisabled' => 'false',
        ];

        foreach ($workspaceContext->activities->read() as $activityInstance) {
            $existingActivities[$activityInstance->friendlyName] = $activityInstance;
        }
        foreach ($requiredActivities as $activityName => $available) {
            if (!isset($existingActivities[$activityName])) {
                $workspaceContext->activities->create($activityName, $available);
            }
        }

        return $workspace;
    }

    /**
     * @param VoiceAccount $account
     *
     * @throws \Exception
     */
    public function deleteWorkspace(VoiceAccount $account)
    {
        try {
            $taskRouter = $this->getClient($account)->taskrouter;
            $taskRouter->workspaces($account->getWorkspaceSid())->delete();
        } catch (RestException $e) {
            if ($e->getStatusCode() === 404) {
                return;
            }

            throw $e;
        }
    }

    /**
     * @param VoiceQueue $queue
     *
     * @throws TwilioException
     *
     * @return bool|\Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance
     */
    public function createTaskQueue(VoiceQueue $queue)
    {
        $workspace  = $this->getQueueWorkspace($queue);
        $activities = [];
        foreach ($workspace->activities->read() as $activityInstance) {
            $activities[$activityInstance->friendlyName] = $activityInstance;
        }

        $reservationSid = $activities['Reserved']->sid;
        $assignmentSid  = $activities['Busy']->sid;
        $taskQueueName  = 'DeskPRO - '.$queue->getName();

        // ensure we don't have task queue with this name
        foreach ($workspace->taskQueues->read() as $existingTaskQueue) {
            if ($existingTaskQueue->friendlyName === $taskQueueName) {
                $existingTaskQueue->delete();
            }
        }

        return $workspace->taskQueues->create($taskQueueName, $reservationSid, $assignmentSid);
    }

    /**
     * @param VoiceQueue $queue
     *
     * @throws TwilioException
     */
    public function updateTaskQueue(VoiceQueue $queue)
    {
        $this->getQueueWorkspace($queue)->taskQueues($queue->getTaskQueueSid())->update([
            'friendlyName'  => 'DeskPRO - '.$queue->getName(),
            'targetWorkers' => 'deskpro_queue_ids HAS '.$queue->getId(),
        ]);
    }

    /**
     * @param VoiceQueue $queue
     *
     * @throws TwilioException
     */
    public function deleteTaskQueue(VoiceQueue $queue)
    {
        try {
            $this->getQueueWorkspace($queue)->taskQueues($queue->getTaskQueueSid())->delete();
        } catch (RestException $e) {
            if ($e->getStatusCode() === 404) {
                return;
            }

            throw $e;
        }
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     *
     * @return WorkerInstance
     */
    public function createWorker(VoiceAccount $account, Person $person)
    {
        return $this->getWorkspace($account)->workers->create(
            $this->getWorkerName($person),
            $this->getWorkerOptions($person)
        );
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     *
     * @return WorkerInstance
     */
    public function updateWorker(VoiceAccount $account, Person $person)
    {
        $workerSid = $person->getAgentData()->getVoiceWorkerSid();
        $options   = array_merge(
            ['friendlyName' => $this->getWorkerName($person)],
            $this->getWorkerOptions($person)
        );

        return $this->getWorkspace($account)->workers($workerSid)->update($options);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $workerSid
     */
    public function deleteWorker(VoiceAccount $account, $workerSid)
    {
        $this->getWorkspace($account)->workers($workerSid)->delete();
    }

    /**
     * @param VoiceAccount $account
     * @param string       $assignmentCallbackUrl
     *
     * @return WorkflowInstance
     */
    public function createOrUpdateWorkflow(VoiceAccount $account, $assignmentCallbackUrl)
    {
        $queues = $this->em->getRepository(VoiceQueue::class)->findBy([
            'account' => $account,
        ]);

        $filters = [];
        foreach ($queues as $queue) {
            $filters[] = [
                'targets' => [
                    [
                        'queue' => $queue->getTaskQueueSid(),
                    ],
                ],
                'filter_friendly_name' => $queue->getName(),
                'expression'           => 'deskpro_queue_id == '.$queue->getId(),
            ];
        }

        $configuration = json_encode([
            'task_routing' => [
                'filters' => $filters,
            ],
        ]);

        $workspace = $this->getWorkspace($account);
        if ($account->getQueueWorkflowSid()) {
            $workflow = $workspace->workflows($account->getQueueWorkflowSid())->update([
                'configuration'         => $configuration,
                'assignmentCallbackUrl' => $assignmentCallbackUrl,
            ]);
        } else {
            $workflow = $workspace->workflows->create('DeskPRO Queue Routing Workflow', $configuration, [
                'assignmentCallbackUrl' => $assignmentCallbackUrl,
            ]);
        }

        return $workflow;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return Client
     */
    protected function getClient(VoiceAccount $account)
    {
        return new Client($account->getAccountSid(), $account->getAuthToken());
    }

    /**
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Taskrouter\V1\WorkspaceContext
     */
    protected function getWorkspace(VoiceAccount $account)
    {
        return $this->getClient($account)->taskrouter->workspaces($account->getWorkspaceSid());
    }

    /**
     * @param VoiceQueue $queue
     *
     * @throws TwilioException
     *
     * @return \Twilio\Rest\Taskrouter\V1\WorkspaceContext
     */
    protected function getQueueWorkspace(VoiceQueue $queue)
    {
        $account = $queue->getAccount();
        if (!$account) {
            throw new TwilioException('Voice queue does not have an account reference.');
        }

        return $this->getWorkspace($account);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string[]
     */
    protected function getAccountNumbersList(VoiceAccount $account)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('n.number')
            ->from(VoiceNumber::class, 'n')
            ->where('n.account = :account')
            ->setParameter('account', $account)
        ;

        $result  = $qb->getQuery()->getArrayResult();
        $numbers = [];

        foreach ($result as $number) {
            $numbers[] = $number['number'];
        }

        return array_fill_keys($numbers, true);
    }

    /**
     * @param Person $person
     *
     * @return string
     */
    protected function getWorkerName(Person $person)
    {
        return 'DeskPRO - '.$person->getName();
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    protected function getWorkerOptions(Person $person)
    {
        $queueIds = $person
            ->getVoiceQueues()
            ->map(function (VoiceQueue $voiceQueue) {
                return $voiceQueue->getId();
            })
            ->toArray()
        ;

        return [
            'attributes' => json_encode([
                'agent_id'          => $person->getId(),
                'agent_name'        => $person->getName(),
                'agent_email'       => $person->getPrimaryEmailAddress(),
                'deskpro_queue_ids' => $queueIds,
                'contact_uri'       => 'deskpro_browser:'.$person->getId(),
            ]),
        ];
    }
}
