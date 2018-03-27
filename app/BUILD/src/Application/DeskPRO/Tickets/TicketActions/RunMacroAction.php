<?php

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\People\PersonContextInterface;

/**
 * Class RunMacroAction.
 */
class RunMacroAction extends AbstractAction implements PersonContextInterface
{
    /**
     * @var int
     */
    private $macroId;

    /**
     * @var Person
     */
    private $personContext;

    /**
     * Constructor.
     *
     * @param int $macroId
     */
    public function __construct($macroId)
    {
        $this->macroId = $macroId;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonContext(Person $person)
    {
        $this->personContext = $person;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $macro = App::getEntityRepository(TicketMacro::class)->find($this->macroId);
        if (!$macro) {
            return;
        }

        $appEnv      = App::$container->get('deskpro.app_env');
        $runMacroIds = $appEnv->getRuntimeVar('run_macro_ids', []);
        if (in_array($macro->getId(), $runMacroIds)) {
            return;
        }

        $runMacroIds[] = $macro->getId();
        $appEnv->setRuntimeVar('run_macro_ids', $runMacroIds);

        $macro->getActionsCollection()->apply($ticket->getTicketLogger(), $ticket, $this->personContext);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            ['action' => 'run_macro', 'macroId' => $this->macroId],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr    = App::getTranslator();
        $macro = App::getEntityRepository(TicketMacro::class)->find($this->macroId);

        return $tr->phrase('agent.tickets.run_macro_action', [
            'macro' => $macro ? $macro->getTitle() : ('<error>Unknown #'.$this->macroId.'</error>'),
        ]);
    }
}
