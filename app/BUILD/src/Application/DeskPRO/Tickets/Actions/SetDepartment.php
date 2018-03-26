<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the department.
 *
 * @option int department_id
 */
class SetDepartment extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('department_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_dep_id = $this->getActionOption('department_id');
        $dep        = $this->getContainer()->getTicketDepartments()->getSettableById($set_dep_id);

        if (!$dep) {
            return;
        }

        $context->getLogger()->debug(sprintf('[SetDepartment] Setting %d %s', $dep->id, $dep->title));
        $ticket->department = $dep;

        // Tmp hack until can figure out why this isn't persisted on at least one server
        // IIS, PHP 5.4.24, WinCache 1.3.4.0
        if (isset(App::$container)) { // if is to prevent this during testing
            App::$container->getDb()->executeUpdate('UPDATE tickets SET department_id = ? WHERE id = ?', [$dep->id, $ticket->id]);
            App::$container->getDb()->executeUpdate('UPDATE tickets_search_active SET department_id = ? WHERE id = ?', [$dep->id, $ticket->id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $set_dep_id    = $this->getActionOption('department_id');
        $ticket_dep_id = $ticket->department ? $ticket->department->id : 0;

        if ($ticket_dep_id == $set_dep_id) {
            $context->getLogger()->debug('[SetDepartment] Skipping, department is the same');

            return true;
        }

        $dep = $this->getContainer()->getTicketDepartments()->getSettableById($set_dep_id);
        if (!$dep) {
            $context->getLogger()->debug('[SetDepartment] Unknown department id: '.$set_dep_id);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'department')) {
            return ['department'];
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
