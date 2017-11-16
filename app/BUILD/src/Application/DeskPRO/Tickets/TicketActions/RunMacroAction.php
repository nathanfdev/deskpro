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
