<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

class NewFeedbackNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\CommunityTopic
     */
    protected $feedback;

    public function __construct(CommunityTopic $feedback)
    {
        parent::__construct();
        $this->feedback = $feedback;
    }

    public function shouldSendBrowserNotification(Person $person)
    {
        if ($this->feedback->getStatus() == 'hidden' && $person->getPref('agent_notif.new_feedback_validate.alert')) {
            return true;
        } elseif ($this->feedback->getStatus() != 'hidden' && $person->getPref('agent_notif.new_feedback.alert')) {
            return true;
        }

        return false;
    }

    public function shouldSendEmailNotification(Person $person)
    {
        if ($this->feedback->getStatus() == 'hidden' && $person->getPref('agent_notif.new_feedback_validate.email')) {
            return true;
        } elseif ($this->feedback->getStatus() != 'hidden' && $person->getPref('agent_notif.new_feedback.email')) {
            return true;
        }

        return false;
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:Community:alert-new-community-topic.html.twig', ['feedback' => $this->feedback, 'notify_data' => ['notify_type' => 'new_feedback']]);
        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.agent_viewmodel_factory')
                ->createAgentNewFeedbackModel($this->feedback);
            $this->sendNewEmailNotifications($viewModel);
        } else {
            $this->sendEmailNotifications(
                'DeskPRO:emails_agent:new-community-topic.html.twig',
                ['feedback' => $this->feedback]
            );
        }

        $this->eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.ui.new-feedback',
            [
                'feedback_id' => $this->feedback->getId(),
            ]
        ));
    }
}
