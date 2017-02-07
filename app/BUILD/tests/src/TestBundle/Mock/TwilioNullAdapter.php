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
            'sid'                   => 'sid'.(++self::$uuid),
            'account_sid'           => 'account_sid',
            'date_created'          => 'date_created',
            'date_updated'          => 'date_updated',
            'default_activity_name' => 'default_activity_name',
            'default_activity_sid'  => 'default_activity_sid',
            'event_callback_url'    => 'event_callback_url',
            'events_filter'         => 'events_filter',
            'friendly_name'         => 'friendly_name',
            'multi_task_enabled'    => 'multi_task_enabled',
            'timeout_activity_name' => 'timeout_activity_name',
            'timeout_activity_sid'  => 'timeout_activity_sid',
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
    public function updateTaskQueue(VoiceQueue $queue)
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
    public function updateAgentWorker(VoiceAccount $account, Person $person, $activityName = null)
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
    public function updateAgentTaskQueue(VoiceAccount $account, Person $person)
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
        ];

        return new TaskQueueInstance($this->getVersion(), $payload, 'workspace_sid');
    }

    /**
     * @return Taskrouter\V1\Workspace\WorkerInstance
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

        ];

        return new Taskrouter\V1\Workspace\WorkerInstance($this->getVersion(), $payload, 'workspace_sid');
    }
}
