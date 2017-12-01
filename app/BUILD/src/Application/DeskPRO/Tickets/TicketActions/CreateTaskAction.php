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

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskAssociatedTicket;
use Application\DeskPRO\Entity\Ticket;
use Orb\Util\Strings;

/**
 * Class CreateTaskAction.
 */
class CreateTaskAction extends AbstractAction implements PermissionableAction
{
    /**
     * @var array
     */
    private $tasks = [];

    /**
     * Constructor.
     *
     * @param string $title
     * @param string $date_due
     * @param int    $date_due_relative
     * @param bool   $use_date_relative
     * @param int    $creator
     * @param int    $assignee
     * @param bool   $public
     * @param bool   $create_by_ticket_agent
     * @param bool   $assign_to_ticket_agent
     */
    public function __construct($title, $date_due, $date_due_relative, $use_date_relative, $creator, $assignee, $public, $create_by_ticket_agent, $assign_to_ticket_agent)
    {
        $this->tasks[] = [
            'title'                  => $title,
            'date_due'               => $date_due,
            'date_due_relative'      => $date_due_relative,
            'use_date_relative'      => $use_date_relative,
            'creator'                => $creator,
            'assignee'               => $assignee,
            'public'                 => $public,
            'create_by_ticket_agent' => $create_by_ticket_agent,
            'assign_to_ticket_agent' => $assign_to_ticket_agent,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        return $person->hasPerm('agent_tasks.use');
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $em = App::$container->get('doctrine.orm.default_entity_manager');

        foreach ($this->tasks as $taskData) {
            $task = new Task();
            $task->setTitle($taskData['title']);
            $task->setVisibility((int) $taskData['public']);

            // set task creator
            if ($taskData['create_by_ticket_agent'] && $ticket->getAgent()) {
                $task->setPerson($ticket->getAgent());
            } elseif ($taskData['creator']) {
                $creator = $em->getRepository(Person::class)->find($taskData['creator']);
                if (!$creator || !$creator->isActiveAgent()) {
                    $creator = null;
                }

                $task->setPerson($creator);
            }

            // set date due
            $dateDue = null;
            if ($taskData['use_date_relative'] && $taskData['date_due_relative']) {
                try {
                    $dateDue = new \DateTime('+'.((int) $taskData['date_due_relative']).' days');
                } catch (\Exception $e) {
                }
            } elseif ($taskData['date_due']) {
                try {
                    $dateDue = new \DateTime($taskData['date_due']);
                } catch (\Exception $e) {
                }
            }
            if ($dateDue) {
                $task->setDateDue($dateDue);
            }

            // set assignee
            if ($taskData['assign_to_ticket_agent'] && $ticket->getAgent()) {
                $task->setAssignedAgent($ticket->getAgent());
            } elseif ($taskData['assignee']) {
                $assignee = Strings::extractRegexMatch('#^(?P<type>.*?):(?P<id>-?\d+)$#', $taskData['assignee'], -1);
                if ($assignee && $assignee['id']) {
                    if ($assignee['type'] === 'agent') {
                        // assign an agent
                        $agent = $em->getRepository(Person::class)->find($assignee['id']);
                        if ($agent && $agent->isActiveAgent()) {
                            $task->setAssignedAgent($agent);
                        }
                    } else {
                        // assign a team
                        $agentTeam = $em->getRepository(AgentTeam::class)->find($assignee['id']);
                        if ($agentTeam) {
                            $task->setAssignedAgentTeam($agentTeam);
                        }
                    }
                }
            }

            // set association
            $association = new TaskAssociatedTicket();
            $association->setTicket($ticket);
            $association->setTask($task);

            $task->addTaskAssociation($association);

            $em->persist($task);
            $em->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $titles = array_map(
            function ($task) {
                return $task['title'];
            },
            $this->tasks
        );

        return App::getTranslator()->phrase('agent.tickets.create_task_action', [
            'title' => implode(', ', $titles),
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param CreateTaskAction $otherAction
     */
    public function merge(ActionInterface $otherAction)
    {
        $this->tasks = array_merge($this->tasks, $otherAction->getTasks());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return array_map(
            function ($task) {
                return array_merge($task, [
                    'action' => 'create_task',
                ]);
            },
            $this->tasks
        );
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
