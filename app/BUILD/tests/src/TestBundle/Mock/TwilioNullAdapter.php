<?php

namespace DpTestSrc\TestBundle\Mock;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioActivities;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Twilio\Rest\Api\V2010;
use Twilio\Rest\Api\V2010\Account\ApplicationInstance;
use Twilio\Rest\Client;
use Twilio\Rest\Taskrouter;
use Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance;
use Twilio\Rest\Taskrouter\V1\WorkspaceInstance;

/**
 * Class TwilioNullAdapter.
 */
class TwilioNullAdapter extends TwilioAdapter
{
    /**
     * @var int
     */
    private static $uuid = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAccount(VoiceAccount $account)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buyNumber(VoiceAccount $account, array $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateNumber(VoiceNumber $number, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createWorkspace(VoiceAccount $account)
    {
        $payload = [
            'sid'                    => 'sid'.(++self::$uuid),
            'account_sid'            => 'account_sid',
            'date_created'           => 'date_created',
            'date_updated'           => 'date_updated',
            'default_activity_name'  => 'default_activity_name',
            'default_activity_sid'   => 'default_activity_sid',
            'event_callback_url'     => 'event_callback_url',
            'events_filter'          => 'events_filter',
            'friendly_name'          => 'friendly_name',
            'multi_task_enabled'     => 'multi_task_enabled',
            'timeout_activity_name'  => 'timeout_activity_name',
            'timeout_activity_sid'   => 'timeout_activity_sid',
            'prioritize_queue_order' => 'prioritize_queue_order',
            'url'                    => 'url',
            'links'                  => 'links',
        ];

        return new WorkspaceInstance($this->getVersion(), $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function createVoicemailTaskQueue(VoiceAccount $account)
    {
        return $this->getTaskQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function createVoicemailWorker(VoiceAccount $account)
    {
        return $this->getWorker();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteWorkspace(VoiceAccount $account)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createTwimlApp(VoiceAccount $account, $requestUrl, $voiceMethod, $statusUrl, $statusMethod)
    {
        $payload = [
            'sid'                     => 'sid',
            'account_sid'             => 'account_sid',
            'api_version'             => 'api_version',
            'date_created'            => 'date_created',
            'date_updated'            => 'date_updated',
            'friendly_name'           => 'friendly_name',
            'message_status_callback' => 'message_status_callback',
            'sms_fallback_method'     => 'sms_fallback_method',
            'sms_fallback_url'        => 'sms_fallback_url',
            'sms_method'              => 'sms_method',
            'sms_status_callback'     => 'sms_status_callback',
            'sms_url'                 => 'sms_url',
            'status_callback'         => 'status_callback',
            'status_callback_method'  => 'status_callback_method',
            'uri'                     => 'uri',
            'voice_caller_id_lookup'  => 'voice_caller_id_lookup',
            'voice_fallback_method'   => 'voice_fallback_method',
            'voice_fallback_url'      => 'voice_fallback_url',
            'voice_method'            => 'voice_method',
            'voice_url'               => 'voice_url',
        ];

        return new ApplicationInstance($this->getVersion(), $payload, 'account_sid');
    }

    /**
     * {@inheritdoc}
     */
    public function createTaskQueue(VoiceQueue $queue)
    {
        return $this->getTaskQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function updateTaskQueue(VoiceQueue $queue, TaskQueueInstance $existingTaskQueue = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTaskQueue(VoiceAccount $account, $queueSid)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskQueues(VoiceAccount $account)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkers(VoiceAccount $account)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function createAgentWorker(VoiceAccount $account, Person $person, $activityName = null)
    {
        return $this->getWorker();
    }

    /**
     * {@inheritdoc}
     */
    public function updateAgentWorker(VoiceAccount $account, Person $person, $activityName = null, WorkerInstance $existingWorker = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteWorker(VoiceAccount $account, $workerSid)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createAgentTaskQueue(VoiceAccount $account, Person $person)
    {
        return $this->getTaskQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function updateAgentTaskQueue(VoiceAccount $account, Person $person, TaskQueueInstance $existingTaskQueue = null)
    {
        return $this->getTaskQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function clearWorkflow(VoiceAccount $account, $assignmentCallbackUrl)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateWorkflow(VoiceAccount $account, $assignmentCallbackUrl)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createWorkerToken(VoiceAccount $account, Person $person)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createPhoneToken(VoiceAccount $account, Person $person)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getActivities(VoiceAccount $account)
    {
        return new TwilioActivities('', '', '', '', '');
    }

    /**
     * {@inheritdoc}
     */
    public function getActivitySid(VoiceAccount $account, $activityName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rejectTaskWorker(VoiceAccount $account, $taskSid, Person $person)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConference(VoiceAccount $account, $conferenceSid)
    {
        $payload = [
            'account_sid'      => 'account_sid',
            'date_created'     => 'date_created',
            'date_updated'     => 'date_updated',
            'api_version'      => 'api_version',
            'friendly_name'    => 'friendly_name',
            'region'           => 'region',
            'sid'              => 'sid',
            'status'           => 'status',
            'uri'              => 'uri',
            'subresource_uris' => 'subresource_uris',
        ];

        return new V2010\Account\ConferenceInstance($this->getVersion(), $payload, 'account_sid');
    }

    /**
     * {@inheritdoc}
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function tryEndConference(VoicePhoneCall $phoneCall)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getActivePhoneCallParticipants(VoicePhoneCall $phoneCall)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isConferenceOnHold(VoicePhoneCall $phoneCall)
    {
        return false;
    }

    /**
     * @return V2010
     */
    private function getVersion()
    {
        return new V2010(new Taskrouter(new Client('username', 'password')));
    }

    /**
     * @return TaskQueueInstance
     */
    private function getTaskQueue()
    {
        $payload = [
            'sid'                       => 'sid'.(++self::$uuid),
            'account_sid'               => 'account_sid',
            'assignment_activity_sid'   => 'assignment_activity_sid',
            'assignment_activity_name'  => 'assignment_activity_name',
            'date_created'              => 'date_created',
            'date_updated'              => 'date_updated',
            'friendly_name'             => 'friendly_name',
            'max_reserved_workers'      => 'max_reserved_workers',
            'reservation_activity_sid'  => 'reservation_activity_sid',
            'reservation_activity_name' => 'reservation_activity_name',
            'target_workers'            => 'target_workers',
            'url'                       => 'url',
            'workspace_sid'             => 'workspace_sid',
            'task_order'                => 'task_order',
            'links'                     => 'links',
        ];

        return new TaskQueueInstance($this->getVersion(), $payload, 'workspace_sid');
    }

    /**
     * @return WorkerInstance
     */
    private function getWorker()
    {
        $payload = [
            'account_sid'         => 'account_sid',
            'activity_name'       => 'activity_name',
            'activity_sid'        => 'activity_sid',
            'attributes'          => 'attributes',
            'available'           => 'available',
            'date_created'        => 'date_created',
            'date_status_changed' => 'date_status_changed',
            'date_updated'        => 'date_updated',
            'friendly_name'       => 'friendly_name',
            'sid'                 => 'sid'.(++self::$uuid),
            'workspace_sid'       => 'workspace_sid',
            'url'                 => 'url',
            'links'               => 'links',

        ];

        return new WorkerInstance($this->getVersion(), $payload, 'workspace_sid');
    }
}
