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
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioActivities;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioAvailableNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioExistingNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioPaginate;
use Doctrine\ORM\EntityManager;
use Twilio\Exceptions\RestException;
use Twilio\Exceptions\TwilioException;
use Twilio\Jwt\ClientToken;
use Twilio\Jwt\TaskRouter\WorkerCapability;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance;
use Twilio\Rest\Client;
use Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance;
use Twilio\Rest\Taskrouter\V1\WorkspaceContext;
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
        $client     = $this->getClient($account);
        $taskRouter = $client->taskrouter;

        $workspaceName = 'DeskPRO Phone';

        // ensure we don't have workspace with this name
        foreach ($taskRouter->workspaces->read() as $existingWorkspace) {
            if ($existingWorkspace->friendlyName === $workspaceName) {
                $existingWorkspace->delete();
            }
        }

        // create the workspace
        $workspace = $taskRouter->workspaces->create($workspaceName, [
            'multiTaskEnabled' => 'false',
            'eventsFilter'     => implode(',', [
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
            ]),
        ]);

        // add additional activity
        $taskRouter->workspaces($workspace->sid)->activities->create('IdleDisabled', 'false');

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
     * @param VoiceAccount $account
     * @param string       $voiceUrl
     * @param string       $voiceMethod
     *
     * @return \Twilio\Rest\Api\V2010\Account\ApplicationInstance
     */
    public function createTwimlApp(VoiceAccount $account, $voiceUrl, $voiceMethod)
    {
        $client  = $this->getClient($account);
        $appName = 'DeskPRO App';

        // ensure we don't have twiml app with this name
        foreach ($client->applications->read() as $existingApp) {
            if ($existingApp->friendlyName === $appName) {
                $existingApp->delete();
            }
        }

        // create twiml app
        $application = $client->applications->create($appName, [
            'voiceUrl'    => $voiceUrl,
            'voiceMethod' => $voiceMethod,
        ]);

        return $application;
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
        $activities = $this->getActivitiesMap($workspace);

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
     * @param string       $activityName
     *
     * @return WorkerInstance
     */
    public function createWorker(VoiceAccount $account, Person $person, $activityName = null)
    {
        $options = $this->getWorkerOptions($person);
        if ($activityName) {
            $options['activitySid'] = $this->getActivitySid($account, $activityName);
        }

        return $this->getWorkspace($account)->workers->create($this->getWorkerName($person), $options);
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     * @param string       $activityName
     *
     * @throws TwilioException
     *
     * @return WorkerInstance
     */
    public function updateWorker(VoiceAccount $account, Person $person, $activityName = null)
    {
        $workerSid = $this->getWorkerSid($person);
        if (!$workerSid) {
            throw new TwilioException('Unable to update voice worker, worker sid does not exist');
        }

        $options = array_merge(
            ['friendlyName' => $this->getWorkerName($person)],
            $this->getWorkerOptions($person)
        );

        if ($activityName) {
            $options['activitySid'] = $this->getActivitySid($account, $activityName);
        }

        return $this->getWorkspace($account)->workers($workerSid)->update($options);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $workerSid
     */
    public function deleteWorker(VoiceAccount $account, $workerSid)
    {
        $worker = $this->getWorkspace($account)->workers($workerSid);
        $worker->update([
            'activitySid' => $this->getActivitySid($account, 'Offline'),
        ]);
        $worker->delete();
    }

    /**
     * @param Person $person
     *
     * @return string
     */
    public static function getWorkerClientName(Person $person)
    {
        return 'deskpro'.$person->getId();
    }

    /**
     * @param Person $person
     *
     * @return string
     */
    public static function getWorkerContactUrl(Person $person)
    {
        return 'client:'.self::getWorkerClientName($person);
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     *
     * @throws TwilioException
     *
     * @return string|null
     */
    public function createWorkerToken(VoiceAccount $account, Person $person)
    {
        $workerSid = $this->getWorkerSid($person);
        if (!$workerSid) {
            return;
        }

        $capability = new WorkerCapability(
            $account->getAccountSid(),
            $account->getAuthToken(),
            $account->getWorkspaceSid(),
            $workerSid
        );

        $capability->allowActivityUpdates();
        $capability->allowReservationUpdates();

        // By default, tokens are good for one hour.
        // Override this default timeout by specifying a new value (in seconds).
        // For example, to generate a token good for 8 hours:
        $token = $capability->generateToken(28800);  // 60 * 60 * 8

        return $token;
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     *
     * @return string|null
     */
    public function createPhoneToken(VoiceAccount $account, Person $person)
    {
        $workerSid = $this->getWorkerSid($person);
        if (!$workerSid) {
            return;
        }

        $capability = new ClientToken($account->getAccountSid(), $account->getAuthToken());
        $capability->allowClientOutgoing($account->getTwimlAppSid());
        $capability->allowClientIncoming(self::getWorkerClientName($person));

        $token = $capability->generateToken(28800);

        return $token;
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
                        'queue'      => $queue->getTaskQueueSid(),
                        'expression' => 'worker.agent_id NOT IN task.rejected_workers',
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
     * @return TwilioActivities
     */
    public function getActivities(VoiceAccount $account)
    {
        $workspace  = $this->getWorkspace($account);
        $activities = $this->getActivitiesMap($workspace);

        return new TwilioActivities(
            $activities['Offline']->sid,
            $activities['Idle']->sid,
            $activities['IdleDisabled']->sid,
            $activities['Busy']->sid,
            $activities['Reserved']->sid
        );
    }

    /**
     * @param VoiceAccount $account
     * @param string       $activityName
     *
     * @throws TwilioException
     *
     * @return string
     */
    public function getActivitySid(VoiceAccount $account, $activityName)
    {
        $workspace  = $this->getWorkspace($account);
        $activities = $this->getActivitiesMap($workspace);

        if (!isset($activities[$activityName])) {
            throw new TwilioException("Activity $activityName does not found");
        }

        return $activities[$activityName]->sid;
    }

    /**
     * @param VoiceAccount $account
     * @param string       $taskSid
     * @param Person       $person
     *
     * @throws TwilioException
     */
    public function rejectTaskWorker(VoiceAccount $account, $taskSid, Person $person)
    {
        $task = $this->getWorkspace($account)->tasks($taskSid)->fetch();
        if (!$task) {
            throw new TwilioException('Task not found');
        }

        $attributes = json_decode($task->attributes, true);
        $task->update([
            'attributes' => json_encode(array_merge($attributes, [
                'rejected_workers' => array_merge(
                    $attributes['rejected_workers'],
                    [$person->getId()]
                ),
            ])),
        ]);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $taskSid
     *
     * @throws TwilioException
     */
    public function endTask(VoiceAccount $account, $taskSid)
    {
        $task = $this->getWorkspace($account)->tasks($taskSid)->fetch();
        if (!$task) {
            throw new TwilioException('Task not found');
        }

        if ($task->assignmentStatus === 'reserved') {
            $task->update([
                'assignmentStatus' => 'canceled',
            ]);
        }
    }

    /**
     * @param VoiceAccount $account
     * @param string       $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\ConferenceInstance
     */
    public function getConference(VoiceAccount $account, $conferenceSid)
    {
        return $this->getConferenceContext($account, $conferenceSid)->fetch();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param bool           $isHold
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        foreach ($participants as $participant) {
            if ($participant->callSid === $phoneCall->getCallSid()) {
                $participant->update([
                    'hold' => $isHold ? 'true' : 'false',
                ]);
            }
        }

        unset($this->cache[$phoneCall->getConferenceSid()]['participants']);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function tryEndConference(VoicePhoneCall $phoneCall)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        if (count($participants) < 2) {
            foreach ($participants as $participant) {
                if ($participant->callSid === $phoneCall->getCallSid()) {
                    $participant->delete();
                }
            }
        }

        unset($this->cache[$phoneCall->getConferenceSid()]['participants']);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return Person[]
     */
    public function getPhoneCallParticipants(VoicePhoneCall $phoneCall)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        $agents = [];
        foreach ($participants as $participant) {
            $agent = $phoneCall->getPersonByCallSid($participant->callSid);
            if ($agent) {
                $agents[] = $agent;
            }
        }

        return $agents;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return bool
     */
    public function isConferenceOnHold(VoicePhoneCall $phoneCall)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        foreach ($participants as $participant) {
            if ($participant->callSid === $phoneCall->getCallSid()) {
                return $participant->hold;
            }
        }

        return false;
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
                'contact_uri'       => self::getWorkerContactUrl($person),
            ]),
        ];
    }

    /**
     * @param Person $person
     *
     * @return string|null
     */
    protected function getWorkerSid(Person $person)
    {
        $agentData = $person->getAgentData();

        return $agentData && $agentData->isVoiceEnabled() ? $agentData->getVoiceWorkerSid() : null;
    }

    /**
     * @param WorkspaceContext $workspace
     *
     * @return ActivityInstance[]
     */
    protected function getActivitiesMap(WorkspaceContext $workspace)
    {
        $activities = [];
        foreach ($workspace->activities->read() as $activityInstance) {
            $activities[$activityInstance->friendlyName] = $activityInstance;
        }

        return $activities;
    }

    /**
     * @param VoiceAccount $account
     * @param string       $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\ConferenceContext
     */
    protected function getConferenceContext(VoiceAccount $account, $conferenceSid)
    {
        return $this->getClient($account)->conferences($conferenceSid);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\Conference\ParticipantInstance[]
     */
    protected function getConferenceParticipants(VoiceAccount $account, $conferenceSid)
    {
        if (!isset($this->cache[$conferenceSid]['participants'])) {
            $conference   = $this->getConferenceContext($account, $conferenceSid);
            $participants = $conference->participants->read();

            $this->cache[$conferenceSid]['participants'] = $participants;
        }

        return $this->cache[$conferenceSid]['participants'];
    }
}
