<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TwitterAccountStatus;

class TweetAssignNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\TwitterAccountStatus
     */
    protected $account_status;

    public function __construct(TwitterAccountStatus $account_status)
    {
        parent::__construct();
        $this->account_status = $account_status;
    }

    public function shouldSendBrowserNotification(Person $agent)
    {
        if (App::getCurrentPerson()->id == $agent->id && !$agent->getPref('agent_notify_override.all.alert')) {
            return false;
        }

        $agent->loadHelper('AgentTeam');

        if ($this->account_status->agent_team && $agent->getPref('agent_notif.tweet_assign_team.alert') && in_array($this->account_status->agent_team->getId(), $agent->getAgentTeamIds())) {
            return true;
        } elseif ($this->account_status->agent && $agent->getPref('agent_notif.tweet_assign_self.alert') && $this->account_status->agent->getId() == $agent->id) {
            return true;
        }

        return false;
    }

    public function shouldSendEmailNotification(Person $agent)
    {
        if (App::getCurrentPerson()->id == $agent->id && !$agent->getPref('agent_notify_override.all.email')) {
            return false;
        }

        $agent->loadHelper('AgentTeam');

        if ($this->account_status->agent_team && $agent->getPref('agent_notif.tweet_assign_team.email') && in_array($this->account_status->agent_team->getId(), $agent->getAgentTeamIds())) {
            return true;
        } elseif ($this->account_status->agent && $agent->getPref('agent_notif.tweet_assign_self.email') && $this->account_status->agent->getId() == $agent->id) {
            return true;
        }

        return false;
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:TwitterStatus:notify-row-assigned.html.twig', [
            'account_status' => $this->account_status,
            'performer'      => App::getCurrentPerson(),
            'notify_data'    => ['notify_type' => 'twitter'],
        ]);
        $this->sendEmailNotifications('DeskPRO:emails_agent:tweet-assigned.html.twig', [
            'account_status' => $this->account_status,
            'performer'      => App::getCurrentPerson(),
        ]);
    }
}
