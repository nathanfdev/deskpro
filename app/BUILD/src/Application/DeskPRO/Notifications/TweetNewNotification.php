<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TwitterAccountStatus;

class TweetNewNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\TwitterAccountStatus
     */
    protected $account_status;

    /**
     * @var \Application\DeskPRO\Entity\TwitterAccountStatus
     */
    protected $reply_account_status;

    public function __construct(TwitterAccountStatus $account_status, TwitterAccountStatus $reply_account_status = null)
    {
        parent::__construct();
        $this->account_status       = $account_status;
        $this->reply_account_status = $reply_account_status;
    }

    public function shouldSendBrowserNotification(Person $agent)
    {
        if ($this->reply_account_status) {
            if ($agent->getPref('agent_notif.tweet_reply.alert')
                && $this->reply_account_status->action_agent
                && $this->reply_account_status->action_agent->id == $agent->id
            ) {
                // already got an alert for this
                return false;
            }
        }

        switch ($this->account_status->status_type) {
            case 'direct': return $agent->getPref('agent_notif.tweet_new_dm.alert');
            case 'reply': return $agent->getPref('agent_notif.tweet_new_reply.alert');
            case 'mention': return $agent->getPref('agent_notif.tweet_new_mention.alert');
            case 'retweet': return $agent->getPref('agent_notif.tweet_new_retweet.alert');
            default: return false;
        }
    }

    public function shouldSendEmailNotification(Person $agent)
    {
        if ($this->reply_account_status) {
            if ($agent->getPref('agent_notif.tweet_reply.email')
                && $this->reply_account_status->action_agent
                && $this->reply_account_status->action_agent->id == $agent->id
            ) {
                // already got an email for this
                return false;
            }
        }

        switch ($this->account_status->status_type) {
            case 'direct': return $agent->getPref('agent_notif.tweet_new_dm.email');
            case 'reply': return $agent->getPref('agent_notif.tweet_new_reply.email');
            case 'mention': return $agent->getPref('agent_notif.tweet_new_mention.email');
            case 'retweet': return $agent->getPref('agent_notif.tweet_new_retweet.email');
            default: return false;
        }
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:TwitterStatus:notify-row-new.html.twig', [
            'account_status' => $this->account_status,
            'notify_data'    => ['notify_type' => 'twitter'],
        ]);
        $this->sendEmailNotifications('DeskPRO:emails_agent:tweet-new.html.twig', [
            'account_status' => $this->account_status,
        ]);
    }
}
