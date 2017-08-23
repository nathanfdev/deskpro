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
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $dateDue;

    /**
     * @var int
     */
    private $creator;

    /**
     * @var int
     */
    private $assignee;

    /**
     * @var bool
     */
    private $public;

    /**
     * Constructor.
     *
     * @param string $title
     * @param string $date_due
     * @param int    $creator
     * @param int    $assignee
     * @param bool   $public
     */
    public function __construct($title, $date_due, $creator, $assignee, $public)
    {
        $this->title    = $title;
        $this->dateDue  = $date_due;
        $this->creator  = $creator;
        $this->assignee = $assignee;
        $this->public   = $public;
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

        $task = new Task();
        $task->setTitle($this->title);
        $task->setVisibility((int) $this->public);

        // set task creator
        if ($this->creator) {
            $creator = $em->getRepository(Person::class)->find($this->creator);
            if (!$creator || !$creator->isActiveAgent()) {
                $creator = null;
            }

            $task->setPerson($creator);
        }

        // set date due
        if ($this->dateDue) {
            try {
                $dateDue = new \DateTime($this->dateDue);
            } catch (\Exception $e) {
                $dateDue = null;
            }

            $task->setDateDue($dateDue);
        }

        // set assignee
        if ($this->assignee) {
            $assignee = Strings::extractRegexMatch('#^(?P<type>.*?):(?P<id>-?\d+)$#', $this->assignee, -1);
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

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        return App::getTranslator()->phrase('agent.tickets.create_task_action', ['title' => $this->title]);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $other_action)
    {
        return $other_action;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            [
                'action'   => 'create_task',
                'title'    => $this->title,
                'date_due' => $this->dateDue,
                'creator'  => $this->creator,
                'assignee' => $this->assignee,
                'public'   => $this->public,
            ],
        ];
    }
}
