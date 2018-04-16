<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

class NewRegistrationNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    public function __construct(Person $person)
    {
        parent::__construct();
        $this->person = $person;
    }

    public function shouldSendBrowserNotification(Person $agent)
    {
        if ($this->person->is_confirmed && $agent->getPref('agent_notif.new_user.alert')) {
            return true;
        }

        return false;
    }

    public function shouldSendEmailNotification(Person $agent)
    {
        if ($this->person->is_confirmed && $agent->getPref('agent_notif.new_user.email')) {
            return true;
        }

        return false;
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:Person:alert-new-registration.html.twig', ['person' => $this->person, 'notify_data' => ['notify_type' => 'new_registration']]);
        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.agent_viewmodel_factory')
                ->createAgentNewRegistrationModel($this->person);
            $this->sendNewEmailNotifications($viewModel);
        } else {
            $this->sendEmailNotifications(
                'DeskPRO:emails_agent:new-registration.html.twig',
                ['person' => $this->person]
            );
        }
    }
}
