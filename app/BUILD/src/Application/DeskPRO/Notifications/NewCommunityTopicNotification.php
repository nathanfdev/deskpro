<?php

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

class NewCommunityTopicNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\CommunityTopic
     */
    protected $communityTopic;

    public function __construct(CommunityTopic $communityTopic)
    {
        parent::__construct();
        $this->communityTopic = $communityTopic;
    }

    public function shouldSendBrowserNotification(Person $person)
    {
        if ($this->communityTopic->getStatus() == 'hidden' && $person->getPref('agent_notif.new_community_validate.alert')) {
            return true;
        } elseif ($this->communityTopic->getStatus() != 'hidden' && $person->getPref('agent_notif.new_community.alert')) {
            return true;
        }

        return false;
    }

    public function shouldSendEmailNotification(Person $person)
    {
        if ($this->communityTopic->getStatus() == 'hidden' && $person->getPref('agent_notif.new_community_validate.email')) {
            return true;
        } elseif ($this->communityTopic->getStatus() != 'hidden' && $person->getPref('agent_notif.new_community.email')) {
            return true;
        }

        return false;
    }

    public function send()
    {
        $this->sendBrowserNotifications(
            'AgentBundle:Community:alert-new-community-topic.html.twig',
            [
                'topic'       => $this->communityTopic,
                'performer'   => App::getCurrentPerson(),
                'notify_data' => ['notify_type' => 'new_community_topic'],
            ]
        );
        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.agent_viewmodel_factory')
                ->createAgentNewCommunityTopicModel($this->communityTopic);
            $this->sendNewEmailNotifications($viewModel);
        } else {
            $this->sendEmailNotifications(
                'DeskPRO:emails_agent:new-community-topic.html.twig',
                ['topic' => $this->communityTopic]
            );
        }

        $this->eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.ui.new-community-topic',
            [
                'topic_id' => $this->communityTopic->getId(),
            ]
        ));
    }
}
