<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TwitterAccountStatus;

class TweetReplyNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\TwitterAccountStatus
     */
    protected $account_status;

    /**
     * @var \Application\DeskPRO\Entity\TwitterAccountStatus
     */
    protected $reply_account_status;

    public function __construct(TwitterAccountStatus $account_status, TwitterAccountStatus $reply_account_status)
    {
        parent::__construct();
        $this->account_status       = $account_status;
        $this->reply_account_status = $reply_account_status;
    }

    public function shouldSendBrowserNotification(Person $agent)
    {
        if (!$agent->getPref('agent_notif.tweet_reply.alert')) {
            return false;
        }

        return $this->reply_account_status->action_agent && $this->reply_account_status->action_agent->id == $agent->id;
    }

    public function shouldSendEmailNotification(Person $agent)
    {
        if (!$agent->getPref('agent_notif.tweet_reply.email')) {
            return false;
        }

        return $this->reply_account_status->action_agent && $this->reply_account_status->action_agent->id == $agent->id;
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:TwitterStatus:notify-row-reply.html.twig', [
            'account_status'       => $this->account_status,
            'reply_account_status' => $this->reply_account_status,
            'notify_data'          => ['notify_type' => 'twitter'],
        ]);
        $this->sendEmailNotifications('DeskPRO:emails_agent:tweet-reply.html.twig', [
            'account_status'       => $this->account_status,
            'reply_account_status' => $this->reply_account_status,
        ]);
    }
}
