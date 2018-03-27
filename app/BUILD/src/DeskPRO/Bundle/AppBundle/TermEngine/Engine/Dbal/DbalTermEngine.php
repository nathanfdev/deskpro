<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTermEngine.
 *
 * This class is a wrapper of DbalTicketFilterEngine allowing convenient interface for Term evaluation. This is rather
 * a fail of OO design, when this lower-level (accordingly to common sense) service uses higher-level
 * DbalTicketFilterEngine. The reason why it exists: can't compile a term not wrapping it in a TicketFilter using
 * existing services.
 *
 * @todo Fix it
 */
class DbalTermEngine
{
    /**
     * @var DbalTicketFilterEngine
     */
    private $ticketFilterEngine;

    /**
     * DbalTermEngine constructor.
     *
     * @param DbalTicketFilterEngine $ticketFilterEngine
     */
    public function __construct(DbalTicketFilterEngine $ticketFilterEngine)
    {
        $this->ticketFilterEngine = $ticketFilterEngine;
    }

    /**
     * @param TermInterface     $term
     * @param TermEngineContext $context
     *
     * @return Query\DbalExecutableQuery
     */
    public function evaluate(TermInterface $term, TermEngineContext $context)
    {
        $filter = new TicketFilter();

        $compositeTerm = new CompositeTerm();
        $compositeTerm->setOp(CompositeTerm::OP_AND);
        $compositeTerm->addTerm($term);

        // add permission terms
        $person   = $context->getAgent();
        $permTerm = new CompositeTerm();
        $permTerm->setOp(CompositeTerm::OP_OR);

        $permTerm->addTerm(new AgentTerm(['agent_ids' => $person->getId()]));
        if ($person->getTeamIds()) {
            $permTerm->addTerm(new AgentTeamTerm(['agent_team_ids' => $person->getTeamIds()]));
        }

        $otherPermTerm = new CompositeTerm();
        $otherPermTerm->setOp(CompositeTerm::OP_AND);

        if ($person->getDisallowedDepartments()) {
            $depPermTerm = new CompositeTerm();
            $depPermTerm->setOp(CompositeTerm::OP_OR);
            $depPermTerm->addTerm(new DepartmentTerm(['department_ids' => $person->getDisallowedDepartments()], TermInterface::OP_NOT));
            $depPermTerm->addTerm(new DepartmentTerm(['department_ids' => null]));

            $otherPermTerm->addTerm($depPermTerm);
        }
        if (!$person->hasPerm('agent_tickets.view_unassigned')) {
            $unassignedTerm = new CompositeTerm();
            $unassignedTerm->setOp(CompositeTerm::OP_OR);
            $unassignedTerm->addTerm(new AgentTerm(['agent_ids' => null], TermInterface::OP_NOT));
            $unassignedTerm->addTerm(new AgentTeamTerm(['agent_team_ids' => null], TermInterface::OP_NOT));

            $otherPermTerm->addTerm($unassignedTerm);
        }
        if (!$person->hasPerm('agent_tickets.view_others')) {
            $otherPermTerm->addTerm(new AgentTerm(['agent_ids' => null]));
            $otherPermTerm->addTerm(new AgentTeamTerm(['agent_team_ids' => null]));
        }

        $permTerm->addTerm($otherPermTerm);
        $compositeTerm->addTerm($permTerm);

        // this is needed to bypass DbalTicketFilterEngine cache
        // @todo generate id from $term to enable caching
        $reflection = new \ReflectionProperty(TicketFilter::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($filter, uniqid());
        $filter->setTerm($compositeTerm);

        return $this->ticketFilterEngine->evaluate($filter, $context);
    }
}
