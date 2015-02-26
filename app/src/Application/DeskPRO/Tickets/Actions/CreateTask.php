<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Form\Type\TaskType;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Strings;

/**
 * Set the status.
 *
 * @option string status
 */
class CreateTask extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritDoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('title', 'creator');
        $options->addValidNames('date_due', 'public', 'assignee');

        return $options;
    }

    protected function getCreator(Person $person)
    {
        $id = (int) $this->getActionOption('creator');
        if (-1 !== $id) {
            $person = $this->getContainer()->getEm()->find('DeskPRO:Person', $id);
            if (!($person && $person['is_agent'] && !$person['is_deleted'])) {
                return null;
            }
        }

        return $person;
    }

    /**
     * {@inheritDoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $task = new Task();
        $form = $this->getContainer()->getFormFactory()->create(new TaskType(), $task);

        if (!$person = $this->getCreator($ticket->person)) {
            $context->getLogger()->debug('[CreateTask] Wrong creator');
        }

        $due_date = $this->getActionOption('date_due', '');

        $assigned_agent_team = null;
        $assigned_agent = null;

        $assignee = $this->getActionOption('assignee', '');
        $context->getLogger()->debug('[CreateTask] assignee is ' . $assignee);
        if ($assignee = Strings::extractRegexMatch('#^(?P<type>.*?):(?P<id>-?\d+)$#', $assignee, -1)) {
            switch ($assignee['type']) {
                case 'agent':
                    $assigned_agent = $assignee['id'];
                    if ($assigned_agent == -1) {
                        $context->getLogger()->debug('[CreateTask] assignee = current agent');
                        if ($context->getPersonContext() && $context->getPersonContext()->is_agent) {
                            $assigned_agent = $context->getPersonContext()->id;
                            $context->getLogger()->debug('[CreateTask] current agent is ' . $assigned_agent);
                        } else {
                            $assigned_agent = null;
                            $context->getLogger()->debug('[CreateTask] current agent is null');
                        }
                    }
                    break;

                case 'team':
                    $assigned_agent_team = $assignee['id'];
                    if (!$assigned_agent_team) {
                        $assigned_agent_team = null;
                    }
                    break;
            }
        }

        $formData = array(
            'title'               => $this->getActionOption('title'),
            'date_due'            => $due_date,
            'visibility'          => (int) $this->getActionOption('public'),
            'person'              => $person['id'],
            'ticket'              => $ticket['id'],
            'assigned_agent'      => $assigned_agent,
            'assigned_agent_team' => $assigned_agent_team
        );

        $form->submit($formData);
        if (!$form->isValid()) {
            $context->getLogger()->debug('[CreateTask] Validation error: ' . (string)$form->getErrorsAsString());

            return;
        }

        $em = $this->getContainer()->getEm();
        $em->persist($task);
        $em->flush();

        // todo postPersist event
        $notify = new \Application\DeskPRO\Notifications\TaskAssignNotification($task);
        $notify->send();

        $context->getLogger()->debug(sprintf('[CreateTask] Created new Task "%s"', $task['title']));
    }


    /**
     * {@inheritDoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        return false;
    }


    /**
     * {@inheritDoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person = $this->getCreator($person)) {
            return array('agent');
        }

        $loader = new AgentPermsPersonDbLoader($person, $this->getContainer()->getEm());
        $perms = $loader->getEffectivePermissions()->toArray();

        if (!$perms['tasks']) {
            return array('tasks');
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
