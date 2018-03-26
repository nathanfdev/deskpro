<?php

/**
 * DeskPRO.
 *
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
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('title', 'creator');
        $options->addValidNames('date_due', 'public', 'assignee', 'offset', 'link');

        return $options;
    }

    protected function getCreator(ExecutorContextInterface $context)
    {
        $id     = (int) $this->getActionOption('creator');
        $person = null;

        if ($id === -1) {
            if ($context->getPersonContext()) {
                $person = $context->getPersonContext();
            }
        } else {
            $person = $this->getContainer()->getEm()->find('DeskPRO:Person', $id);
        }

        if (!($person && $person['is_agent'] && !$person['is_deleted'])) {
            $person = null;
        }

        return $person;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $task = new Task();

        $form = $this->getContainer()->getFormFactory()->create(
            new TaskType(),
            $task,
            ['timezone' => 'UTC']
        );

        if (!$person = $this->getCreator($context)) {
            $context->getLogger()->debug('[CreateTask] Wrong creator');
        }

        if ($due_date = $this->getActionOption('date_due', '')) {
            $due_date = new \DateTime($due_date, new \DateTimeZone('UTC'));
            $due_date->setTime(23, 59, 59);
            $due_date->modify((int) $this->getActionOption('offset').'hours');
            $due_date = $due_date->format('Y-m-d H:i:s');
        }

        $assigned_agent_team = null;
        $assigned_agent      = null;

        $assignee = $this->getActionOption('assignee', '');
        $context->getLogger()->debug('[CreateTask] assignee is '.$assignee);
        if ($assignee = Strings::extractRegexMatch('#^(?P<type>.*?):(?P<id>-?\d+)$#', $assignee, -1)) {
            switch ($assignee['type']) {
                case 'agent':
                    $assigned_agent = $assignee['id'];
                    if ($assigned_agent == -1) {
                        $context->getLogger()->debug('[CreateTask] assignee = current agent');
                        if ($context->getPersonContext() && $context->getPersonContext()->is_agent) {
                            $assigned_agent = $context->getPersonContext()->id;
                            $context->getLogger()->debug('[CreateTask] current agent is '.$assigned_agent);
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

        $formData = [
            'title'               => $this->getActionOption('title'),
            'date_due'            => $due_date,
            'visibility'          => (int) $this->getActionOption('public'),
            'person'              => $person['id'],
            'ticket'              => null,
            'assigned_agent'      => $assigned_agent,
            'assigned_agent_team' => $assigned_agent_team,
        ];

        if ((int) $this->getActionOption('link')) {
            $formData['ticket'] = $ticket->id;
        }

        $form->submit($formData);
        if (!$form->isValid()) {
            $context->getLogger()->debug('[CreateTask] Validation error: '.(string) $form->getErrorsAsString());

            return;
        }

        $em = $this->getContainer()->getEm();
        $em->persist($task);
        $em->flush();
        $ticket->getStateChangeRecorder()->record('new_tasks', null, $task);

        // todo postPersist event
        $notify = new \Application\DeskPRO\Notifications\TaskAssignNotification($task);
        $notify->send();

        $context->getLogger()->debug(sprintf('[CreateTask] Created new Task "%s"', $task['title']));
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person = $this->getCreator($context)) {
            return ['agent'];
        }

        $loader = new AgentPermsPersonDbLoader($person, $this->getContainer()->getEm());
        $perms  = $loader->getEffectivePermissions()->toArray();

        if (!$perms['tasks']) {
            return ['tasks'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
