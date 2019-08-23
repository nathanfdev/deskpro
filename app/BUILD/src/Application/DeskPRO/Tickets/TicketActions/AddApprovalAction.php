<?php

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;

/**
 * Class AddApprovalAction
 *
 * @package Application\DeskPRO\Tickets\TicketActions
 */
class AddApprovalAction extends AbstractAction implements PersonContextInterface, PermissionableAction
{
    /**
     * @var string|int
     */
    protected $approval_template_id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Person
     */
    protected $person_context;

    /**
     * @var AbstractBaseApproval
     */
    protected $approval;

    /**
     * AddApprovalAction constructor.
     *
     * @param string|int $approval_template_id
     * @param string $description
     * @throws \Exception
     */
    public function __construct($approval_template_id, $description) {
        $this->approval_template_id = $approval_template_id;
        $this->description = $description;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function apply(Ticket $ticket)
    {
        /** @var TicketApproval $approval */
        $this->approval = TicketApproval::createFromTemplate(
            $this->getTemplate()
        );

        $this->approval->setTicket($ticket);
        $this->approval->setDescription($this->description);

        $manager = App::getContainer()->get('approval.approval_manager');

        $manager->saveApproval(
            $this->approval,
            $manager->createContext(ExecutorContextInterface::METHOD_WEB, $this->person_context)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            ['action' => 'add_approval', 'approval_template_id' => $this->approval_template_id],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        $template = $this->getTemplate();

        if ($template) {
            return $as_html
                ? sprintf(
                    '%s: <span class="with-approval-template">%s</span>',
                    $tr->phrase('agent.tickets.add_approval'),
                    htmlspecialchars($template->getName(), \ENT_QUOTES)
                )
                : sprintf(
                    '%s: %s',
                    $tr->phrase('agent.tickets.add_approval'),
                    $template->getName()
                )
            ;
        }

        return $tr->phrase('agent.tickets.approval_add_approval_template_not_found');
    }

    /**
     * @return ApprovalTemplate|null
     */
    private function getTemplate()
    {
        return App::getContainer()->getEm()->getRepository(ApprovalTemplate::class)->findOneBy([
            'id' => $this->approval_template_id,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        return $person->PermissionsManager->TicketChecker->canAddApproval($ticket);
    }

    /**
     * @return AbstractBaseApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }
}
