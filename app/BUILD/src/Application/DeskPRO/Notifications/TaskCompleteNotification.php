<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;

class TaskCompleteNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\Task
     */
    protected $task;

    public function __construct(Task $task)
    {
        parent::__construct();
        $this->task = $task;
    }

    public function shouldSendBrowserNotification(Person $agent)
    {
        if (App::getCurrentPerson()->id == $agent->id && !$agent->getPref('agent_notify_override.all.alert')) {
            return false;
        }

        if (!$this->task->person) {
            return false;
        }

        if ($this->task->person->getId() == $agent->id && $agent->getPref('agent_notif.task_complete.alert')) {
            return true;
        }

        return false;
    }

    public function shouldSendEmailNotification(Person $agent)
    {
        if (App::getCurrentPerson()->id == $agent->id && !$agent->getPref('agent_notify_override.all.email')) {
            return false;
        }

        if (!$this->task->person) {
            return false;
        }

        if ($this->task->person->getId() == $agent->id && $agent->getPref('agent_notif.task_complete.email')) {
            return true;
        }

        return false;
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:Task:notify-row-completed.html.twig', [
            'task'        => $this->task,
            'performer'   => App::getCurrentPerson(),
            'notify_data' => ['notify_type' => 'tasks'],
        ]);
        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.agent_viewmodel_factory')
                ->createAgentTaskCompletedModel($this->task, App::getCurrentPerson());
            $this->sendNewEmailNotifications($viewModel);
        } else {
            $this->sendEmailNotifications('DeskPRO:emails_agent:task-completed.html.twig', [
                'task'      => $this->task,
                'performer' => App::getCurrentPerson(),
            ]);
        }
    }
}
