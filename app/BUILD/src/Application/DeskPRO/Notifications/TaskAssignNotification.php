<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;

class TaskAssignNotification extends AbstractAgentNotification
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
        if (App::getCurrentPerson() === $agent && !$agent->getPref('agent_notify_override.all.alert')) {
            return false;
        }

        $agent->loadHelper('AgentTeam');

        if ($this->task->assigned_agent_team && $agent->getPref('agent_notif.task_assign_team.alert') && in_array($this->task->assigned_agent_team->getId(), $agent->getAgentTeamIds())) {
            return true;
        } elseif ($this->task->assigned_agent && $agent->getPref('agent_notif.task_assign_self.alert') && $this->task->assigned_agent->getId() == $agent->id) {
            return true;
        }

        return false;
    }

    public function shouldSendEmailNotification(Person $agent)
    {
        if (App::getCurrentPerson() === $agent && !$agent->getPref('agent_notify_override.all.email')) {
            return false;
        }

        $agent->loadHelper('AgentTeam');

        if ($this->task->assigned_agent_team && $agent->getPref('agent_notif.task_assign_team.email') && in_array($this->task->assigned_agent_team->getId(), $agent->getAgentTeamIds())) {
            return true;
        } elseif ($this->task->assigned_agent && $agent->getPref('agent_notif.task_assign_self.email') && $this->task->assigned_agent->getId() == $agent->id) {
            return true;
        }

        return false;
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:Task:notify-row-assigned.html.twig', [
            'task'        => $this->task,
            'performer'   => App::getCurrentPerson(),
            'notify_data' => ['notify_type' => 'tasks'],
        ]);
        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.agent_viewmodel_factory')
                ->createAgentTaskAssignedModel($this->task, App::getCurrentPerson());
            $this->sendNewEmailNotifications($viewModel);
        } else {
            $this->sendEmailNotifications(
                'DeskPRO:emails_agent:task-assigned.html.twig',
                [
                    'task'      => $this->task,
                    'performer' => App::getCurrentPerson(),
                ]
            );
        }
    }
}
