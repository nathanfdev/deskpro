<?php

namespace DeskPRO\Bundle\AppBundle\Twilio;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use DeskPRO\Bundle\AppBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioActivities;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioAvailableNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioExistingNumber;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioPaginate;
use DeskPRO\Bundle\AppBundle\Twilio\Rest\Proxy\ClientProxy;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;
use Twilio\Exceptions\TwilioException;
use Twilio\Jwt\ClientToken;
use Twilio\Jwt\TaskRouter\WorkerCapability;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance;
use Twilio\Rest\Client;
use Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance;
use Twilio\Rest\Taskrouter\V1\WorkspaceContext;
use Twilio\Values;

/**
 * Class TwilioAdapter.
 */
class TwilioAdapter
{
    const VOICEMAIL_WAITING_TIMEOUT = 30;
    const WORKFLOW_NAME             = 'DeskPRO Queue Routing Workflow';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var VoiceSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var array
     */
    private $activities;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param VoiceSettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, VoiceSettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param AgentData $agentData
     *
     * @return string
     */
    public static function getActivityStatus(AgentData $agentData)
    {
        $status = $agentData->getAvailableStatus();
        if (!$agentData->isAgentCallsEnabled()) {
            $status = AgentData::AVAILABLE_STATUS_OFFLINE;
        }

        return ucfirst(Strings::dashToCamelCase($status));
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
            $client  = $this->getClient($account);
            $result  = $client->availablePhoneNumbers($countryCode)->$type->page($options);
            $exclude = $this->getAccountNumbersList($account);
            $prices  = $client->pricing->phoneNumbers->countries($countryCode)->fetch();

            $priceTypeMap = [
                'local'     => 'local',
                'national'  => 'local',
                'mobile'    => 'mobile',
                'toll free' => 'tollFree',
            ];

            $pricesMap = [];
            foreach ($prices->phoneNumberPrices as $price) {
                $pricesMap[$priceTypeMap[$price['number_type']]] = $price['current_price'];
            }

            foreach ($result as $apiNumber) {
                $numbers[] = new TwilioAvailableNumber(
                    $apiNumber,
                    $account,
                    isset($exclude[$apiNumber->phoneNumber]),
                    $type,
                    $pricesMap[$type],
                    $prices->priceUnit
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
            if (strtolower($existingWorkspace->friendlyName) === strtolower($workspaceName)) {
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
        $taskRouter->workspaces($workspace->sid)->activities->create('IdleDisabled', ['available' => false]);

        return $workspace;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance
     */
    public function createVoicemailTaskQueue(VoiceAccount $account)
    {
        $workspace  = $this->getWorkspace($account);
        $activities = $this->getActivitiesMap($workspace);

        $reservationSid = $activities['Reserved']->sid;
        $assignmentSid  = $activities['Busy']->sid;

        $taskQueue = $workspace->taskQueues->create('DeskPRO - Voicemail', $reservationSid, $assignmentSid, [
            'targetWorkers' => "deskpro_queue_id == 'voicemail'",
        ]);

        return $taskQueue;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return WorkerInstance
     */
    public function createVoicemailWorker(VoiceAccount $account)
    {
        $workspace = $this->getWorkspace($account);
        $worker    = $workspace->workers->create('DeskPRO - Voicemail', [
            'activitySid' => $this->getActivitySid($account, 'Idle'),
            'attributes'  => json_encode([
                'deskpro_queue_id' => 'voicemail',
            ]),
        ]);

        return $worker;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return WorkerInstance
     */
    public function getVoicemailWorker(VoiceAccount $account)
    {
        return $this->getWorkspace($account)->workers($account->getVoicemailWorkerSid())->fetch();
    }

    /**
     * @param VoiceAccount $account
     * @param string       $activityName
     *
     * @return WorkerInstance
     */
    public function updateVoicemailWorkerActivity(VoiceAccount $account, $activityName)
    {
        $this->getWorkspace($account)->workers($account->getVoicemailWorkerSid())->update([
            'activitySid' => $this->getActivitySid($account, $activityName),
        ]);
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
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
        }
    }

    /**
     * @param VoiceAccount $account
     * @param string       $requestUrl
     * @param string       $voiceMethod
     * @param string       $statusUrl
     * @param string       $statusMethod
     *
     * @return \Twilio\Rest\Api\V2010\Account\ApplicationInstance
     */
    public function createTwimlApp(VoiceAccount $account, $requestUrl, $voiceMethod, $statusUrl, $statusMethod)
    {
        $client  = $this->getClient($account);
        $appName = 'DeskPRO App';

        // ensure we don't have twiml app with this name
        foreach ($client->applications->read() as $existingApp) {
            if (strtolower($existingApp->friendlyName) === strtolower($appName)) {
                $existingApp->delete();
            }
        }

        // create twiml app
        $application = $client->applications->create($appName, [
            'voiceUrl'             => $requestUrl,
            'voiceMethod'          => $voiceMethod,
            'statusCallback'       => $statusUrl,
            'statusCallbackMethod' => $statusMethod,
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
        $taskQueueName  = $this->getQueueName($queue);

        // ensure we don't have task queue with this name
        $existingTaskQueues = $workspace->taskQueues->read([
            'friendlyName' => strtolower($taskQueueName),
        ]);

        foreach ($existingTaskQueues as $existingTaskQueue) {
            if (strtolower($existingTaskQueue->friendlyName) === strtolower($taskQueueName)) {
                $existingTaskQueue->delete();
            }
        }

        return $workspace->taskQueues->create($taskQueueName, $reservationSid, $assignmentSid, [
            'targetWorkers'      => 'deskpro_queue_ids HAS '.$queue->getId(),
            'maxReservedWorkers' => $queue->getMaxQueueSize(),
        ]);
    }

    /**
     * @param VoiceQueue        $queue
     * @param TaskQueueInstance $existingTaskQueue
     *
     * @throws TwilioException
     */
    public function updateTaskQueue(VoiceQueue $queue, TaskQueueInstance $existingTaskQueue = null)
    {
        $options = [
            'friendlyName'       => $this->getQueueName($queue),
            'targetWorkers'      => 'deskpro_queue_ids HAS '.$queue->getId(),
            'maxReservedWorkers' => $queue->getMaxQueueSize(),
        ];

        if ($existingTaskQueue
            && $existingTaskQueue->sid === $queue->getTaskQueueSid()
            && $existingTaskQueue->friendlyName === $options['friendlyName']
            && $existingTaskQueue->targetWorkers === $options['targetWorkers']
            && $existingTaskQueue->maxReservedWorkers === $options['maxReservedWorkers']
        ) {
            // nothing was changed, skipping
            return;
        }

        $this->getQueueWorkspace($queue)->taskQueues($queue->getTaskQueueSid())->update($options);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $queueSid
     *
     * @throws TwilioException
     */
    public function deleteTaskQueue(VoiceAccount $account, $queueSid)
    {
        try {
            $this->getWorkspace($account)->taskQueues($queueSid)->delete();
        } catch (RestException $e) {
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
        }
    }

    /**
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance[]
     */
    public function getTaskQueues(VoiceAccount $account)
    {
        return $this->getWorkspace($account)->taskQueues->read();
    }

    /**
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance[]
     */
    public function getWorkers(VoiceAccount $account)
    {
        return $this->getWorkspace($account)->workers->read();
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     * @param string       $activityName
     *
     * @return WorkerInstance
     */
    public function createAgentWorker(VoiceAccount $account, Person $person, $activityName = null)
    {
        $options = $this->getWorkerOptions($person);
        if ($activityName) {
            $options['activitySid'] = $this->getActivitySid($account, $activityName);
        }

        $workerName   = $this->getWorkerName($person);
        $workers      = $this->getWorkspace($account)->workers;
        $existWorkers = $workers->read([
            'friendlyName' => $workerName,
        ]);

        if (count($existWorkers)) {
            $worker = current($existWorkers);
            $worker = $workers->getContext($worker->sid)->update(array_merge($options, [
                'friendlyName' => $workerName,
            ]));
        } else {
            $worker = $workers->create($workerName, $options);
        }

        return $worker;
    }

    /**
     * @param VoiceAccount   $account
     * @param Person         $person
     * @param string         $activityName
     * @param WorkerInstance $existingWorker
     *
     * @throws TwilioException
     *
     * @return WorkerInstance
     */
    public function updateAgentWorker(VoiceAccount $account, Person $person, $activityName = null, WorkerInstance $existingWorker = null)
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

        if ($existingWorker
            && $existingWorker->sid === $workerSid
            && $existingWorker->friendlyName === $options['friendlyName']
            && $existingWorker->attributes === $options['attributes']
            && (($activityName && $existingWorker->activitySid === $options['activitySid']) || !$activityName)
        ) {
            // nothing was changed, skipping
            return;
        }

        return $this->getWorkspace($account)->workers($workerSid)->update($options);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $workerSid
     *
     * @throws \Exception
     */
    public function deleteWorker(VoiceAccount $account, $workerSid)
    {
        $worker = $this->getWorkspace($account)->workers($workerSid);

        try {
            $worker->update([
                'activitySid' => $this->getActivitySid($account, 'Offline'),
            ]);
            $worker->delete();
        } catch (RestException $e) {
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
        }
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     */
    public function rejectAgentWorkerReservations(VoiceAccount $account, Person $person)
    {
        $workerSid    = $this->getWorkerSid($person);
        $workspace    = $this->getWorkspace($account);
        $reservations = $workspace->workers($workerSid)->reservations->read([
            'reservationStatus' => 'pending',
        ]);

        foreach ($reservations as $reservation) {
            try {
                $workspace->workers($workerSid)->reservations($reservation->sid)->update([
                    'reservationStatus' => 'rejected',
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance
     */
    public function createAgentTaskQueue(VoiceAccount $account, Person $person)
    {
        $workspace  = $this->getWorkspace($account);
        $activities = $this->getActivitiesMap($workspace);

        $reservationSid = $activities['Reserved']->sid;
        $assignmentSid  = $activities['Busy']->sid;
        $taskQueueName  = $this->getWorkerName($person);

        // ensure we don't have task queue with this name
        $existingTaskQueues = $workspace->taskQueues->read([
            'friendlyName' => strtolower($taskQueueName),
        ]);

        foreach ($existingTaskQueues as $existingTaskQueue) {
            if (strtolower($existingTaskQueue->friendlyName) === strtolower($taskQueueName)) {
                $existingTaskQueue->delete();
            }
        }

        return $workspace->taskQueues->create($taskQueueName, $reservationSid, $assignmentSid, [
            'maxReservedWorkers' => 1,
            'targetWorkers'      => 'agent_id == '.$person->getId(),
        ]);
    }

    /**
     * @param VoiceAccount      $account
     * @param Person            $person
     * @param TaskQueueInstance $existingTaskQueue
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance
     */
    public function updateAgentTaskQueue(VoiceAccount $account, Person $person, TaskQueueInstance $existingTaskQueue = null)
    {
        $workspace = $this->getWorkspace($account);
        $options   = [
            'friendlyName'       => $this->getWorkerName($person),
            'maxReservedWorkers' => 1,
            'targetWorkers'      => 'agent_id == '.$person->getId(),
        ];

        if ($existingTaskQueue
            && $existingTaskQueue->sid === $person->getAgentData()->getVoiceTaskQueueSid()
            && $existingTaskQueue->friendlyName === $options['friendlyName']
            && $existingTaskQueue->targetWorkers === $options['targetWorkers']
            && $existingTaskQueue->maxReservedWorkers === $options['maxReservedWorkers']
        ) {
            // nothing was changed, skipping
            return;
        }

        $workspace->taskQueues($person->getAgentData()->getVoiceTaskQueueSid())->update($options);
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
     * @param string $caller
     *
     * @return bool
     */
    public static function isWorkerContactUrl($caller)
    {
        return strpos($caller, 'client:') === 0;
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
     * @throws \Exception
     *
     * @return WorkflowInstance
     */
    public function clearWorkflow(VoiceAccount $account, $assignmentCallbackUrl)
    {
        // force delete old workflow to avoid twilio FK errors
        // just cleaning workflow still keeps task queue sids for some reason
        $workspace = $this->getWorkspace($account);
        try {
            $workspace->workflows($account->getQueueWorkflowSid())->delete();
        } catch (RestException $e) {
            // unexpected response exception, bubble the exception and stop syncing
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                // try to delete by name
                $existWorkflows = $workspace->workflows->read([
                    'FriendlyName' => self::WORKFLOW_NAME,
                ]);

                foreach ($existWorkflows as $existWorkflow) {
                    $existWorkflow->delete();
                }
            } else {
                throw $e;
            }
        }

        $configuration = json_encode([
            'task_routing' => [
                'filters' => [
                    [
                        'targets' => [
                            [
                                'queue'      => $account->getVoicemailQueueSid(),
                                'expression' => '1 = 1',
                            ],
                        ],
                        'filter_friendly_name' => 'Voicemail',
                        'expression'           => '1 = 1',
                    ],
                ],
            ],
        ]);

        // create a new empty workflow
        return $workspace->workflows->create(self::WORKFLOW_NAME, $configuration, [
            'assignmentCallbackUrl' => $assignmentCallbackUrl,
        ]);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $assignmentCallbackUrl
     *
     * @throws \Exception
     *
     * @return WorkflowInstance
     */
    public function createOrUpdateWorkflow(VoiceAccount $account, $assignmentCallbackUrl)
    {
        $filters = [];

        // get filters based on voice queues
        $queues = $account->getQueues();
        foreach ($queues as $queue) {
            $filters[] = [
                'targets' => [
                    [
                        'queue'      => $queue->getTaskQueueSid(),
                        'expression' => 'worker.agent_id NOT IN task.rejected_workers',
                        'priority'   => 1,
                        'timeout'    => $queue->getVoicemailTimeout() ?: self::VOICEMAIL_WAITING_TIMEOUT,
                    ],
                    [
                        'queue' => $account->getVoicemailQueueSid(),
                    ],
                ],
                'filter_friendly_name' => $this->getQueueName($queue),
                'expression'           => 'deskpro_queue_id == '.$queue->getId(),
            ];
        }

        // get filters for specific agents
        $agents = $this->em->getRepository(AgentData::class)->findBy([
            'isVoiceEnabled' => true,
        ]);

        foreach ($agents as $agent) {
            $filters[] = [
                'targets' => [
                    [
                        'queue'      => $agent->getVoiceTaskQueueSid(),
                        'expression' => 'worker.agent_id NOT IN task.rejected_workers',
                        'priority'   => 1,
                        'timeout'    => $this->settingsResolver->getVoiceSettings()->getAgentVoicemailTimeout(),
                    ],
                    [
                        'queue' => $account->getVoicemailQueueSid(),
                    ],
                ],
                'filter_friendly_name' => $this->getWorkerName($agent->getPerson()),
                'expression'           => 'agent_id == '.$agent->getPerson()->getId(),
            ];
        }

        $configuration = json_encode([
            'task_routing' => [
                'filters' => $filters,
            ],
        ]);

        $workspace = $this->getWorkspace($account);
        if ($account->getQueueWorkflowSid()) {
            try {
                $workflow = $workspace->workflows($account->getQueueWorkflowSid())->update([
                    'configuration'         => $configuration,
                    'assignmentCallbackUrl' => $assignmentCallbackUrl,
                ]);
            } catch (RestException $e) {
                if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                    $workflow = $workspace->workflows->create(self::WORKFLOW_NAME, $configuration, [
                        'assignmentCallbackUrl' => $assignmentCallbackUrl,
                    ]);
                } else {
                    throw $e;
                }
            }
        } else {
            $workflow = $workspace->workflows->create(self::WORKFLOW_NAME, $configuration, [
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
        try {
            $task = $this->getWorkspace($account)->tasks($taskSid)->fetch();
        } catch (RestException $e) {
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
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
     * @param Person       $person
     */
    public function cancelForwardingCall(VoiceAccount $account, Person $person)
    {
        $agentData = $person->getAgentData();
        if (!$agentData || !$agentData->getForwardingNumber()) {
            return;
        }

        $forwardingCalls = $this->getClient($account)->calls->read([
            'to'     => $agentData->getForwardingNumber(),
            'status' => 'ringing',
        ]);

        foreach ($forwardingCalls as $forwardingCall) {
            try {
                $forwardingCall->update([
                    'status' => 'canceled',
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function cancelForwardingCalls(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        $client  = $this->getClient($account);

        foreach ($phoneCall->getForwardingSids() as $forwardingSid) {
            try {
                $forwardingCall = $client->calls($forwardingSid)->fetch();
                if ($forwardingCall->status === 'ringing') {
                    $forwardingCall->update([
                        'status' => 'canceled',
                    ]);
                }
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @return bool
     */
    public function acceptTaskInForwardingCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        $account = $phoneCall->getNumber()->getAccount();

        try {
            $reservations = $this->getWorkspace($account)->tasks($phoneCall->getTaskSid())->reservations->read();
            foreach ($reservations as $reservation) {
                if ($agent->getAgentData()->getVoiceWorkerSid() === $reservation->workerSid) {
                    $reservation->update([
                        'reservationStatus' => 'accepted',
                    ]);
                } else {
                    $reservation->update([
                        'reservationStatus' => 'rejected',
                    ]);
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param VoiceAccount $account
     * @param string       $taskSid
     *
     * @throws TwilioException
     */
    public function endTask(VoiceAccount $account, $taskSid)
    {
        try {
            $task = $this->getWorkspace($account)->tasks($taskSid)->fetch();
        } catch (RestException $e) {
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
        }

        if ($task->sid) {
            $task->delete();
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
     * @param string         $callSid
     * @param bool           $mute
     */
    public function muteParticipant(VoicePhoneCall $phoneCall, $callSid, $mute)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        foreach ($participants as $participant) {
            if ($participant->callSid === $callSid) {
                $participant->update([
                    'muted' => $mute ? 'true' : 'false',
                ]);
            }
        }

        unset($this->cache[$phoneCall->getConferenceSid()]['participants']);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param bool           $isHold
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        if (!$phoneCall->getUserParticipants()->count()) {
            return;
        }

        /** @var VoicePhoneCallParticipantUser $userParticipant */
        $userParticipant = $phoneCall->getUserParticipants()->first();

        foreach ($participants as $participant) {
            if ($participant->callSid === $userParticipant->getCallSid()) {
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
            $userParticipants = $phoneCall->getUserParticipants()->map(function (VoicePhoneCallParticipantUser $participant) {
                return $participant->getCallSid();
            });

            foreach ($participants as $participant) {
                if ($userParticipants->contains($participant->callSid)) {
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
    public function getActivePhoneCallParticipants(VoicePhoneCall $phoneCall)
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
     * @param VoiceNumber $number
     * @param string      $toNumber
     * @param array       $options
     *
     * @return \Twilio\Rest\Api\V2010\Account\CallInstance
     */
    public function callNumber(VoiceNumber $number, $toNumber, array $options = [])
    {
        $account = $number->getAccount();
        $client  = $this->getClient($account);

        return $client->calls->create($toNumber, $number->getNumber(), $options);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $callSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\CallInstance
     */
    public function cancelCall(VoiceAccount $account, $callSid)
    {
        return $this->getClient($account)->calls($callSid)->update([
            'status' => 'canceled',
        ]);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return Client
     */
    protected function getClient(VoiceAccount $account)
    {
        $client = new ClientProxy($account->getAccountSid(), $account->getAuthToken());
        $client
            ->setProxyUsername($this->settingsResolver->getProxyUsername())
            ->setProxyPassword($this->settingsResolver->getProxyPassword())
            ->setApiProxyUrl($this->settingsResolver->getProxyApiUrl())
            ->setTaskRouterProxyUrl($this->settingsResolver->getProxyTaskRouterUrl())
            ->setAccountsProxyUrl($this->settingsResolver->getProxyAccountsUrl())
            ->setProxyPricingUrl($this->settingsResolver->getProxyPricingUrl())
        ;

        return $client;
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
        return 'DeskPRO - Agent #'.$person->getId();
    }

    /**
     * @param VoiceQueue $queue
     *
     * @return string
     */
    protected function getQueueName(VoiceQueue $queue)
    {
        return 'DeskPRO - Queue #'.$queue->getId();
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
            ->filter(function (VoiceQueueAgent $voiceQueue) {
                return $voiceQueue->isEnabled();
            })
            ->map(function (VoiceQueueAgent $voiceQueue) {
                return $voiceQueue->getQueue()->getId();
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
        if (null === $this->activities) {
            $this->activities = [];
            foreach ($workspace->activities->read() as $activityInstance) {
                $this->activities[$activityInstance->friendlyName] = $activityInstance;
            }
        }

        return $this->activities;
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
