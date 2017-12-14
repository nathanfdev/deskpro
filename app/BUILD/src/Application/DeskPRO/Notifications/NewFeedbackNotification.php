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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

class NewFeedbackNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\Feedback
     */
    protected $feedback;

    public function __construct(Feedback $feedback)
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
        $this->sendBrowserNotifications('AgentBundle:Feedback:alert-new-feedback.html.twig', ['feedback' => $this->feedback, 'notify_data' => ['notify_type' => 'new_feedback']]);
        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.agent_viewmodel_factory')
                ->createNewFeedbackModel($this->feedback);
            $this->sendNewEmailNotifications($viewModel);
        } else {
            $this->sendEmailNotifications(
                'DeskPRO:emails_agent:new-feedback.html.twig',
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
