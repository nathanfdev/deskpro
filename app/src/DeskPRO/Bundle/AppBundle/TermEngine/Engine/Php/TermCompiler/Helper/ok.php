<?php

class class552c7c0fb7327 implements DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerInterface
{
    protected $context;
    protected $expression_language;

    public function __construct(
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext $context,
        \DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage $expression_language
    )
    {
        $this->context = $context;
        $this->expression_language = $expression_language;
    }

    public function isTicketMatch(\Application\DeskPRO\Entity\Ticket $ticket)
    {
        return (bool)$this->mainCheck($ticket);
    }

    public function func552c7c0fad75f(\Application\DeskPRO\Entity\Ticket $ticket)
    {
        $check = false;
        $check = (
            (
            in_array(
                $ticket->getAgentId(),
                \Orb\Util\Arrays::flatten(
                    array(0)
                )
            )
            )
            ||
            (
                !$ticket->getAgentId()
                &&
                !count(
                    \Orb\Util\Arrays::flatten(array(0))
                )
            )
        );
        return $check;
    }

    public function func552c7c0fae0da(\Application\DeskPRO\Entity\Ticket $ticket)
    {
        $check = false;
        $check = (
            (
            in_array(
                $ticket->getAgentTeamId(),
                \Orb\Util\Arrays::flatten(
                    array($this->evaluateExpression('agent.getTeamIds()'))
                )
            )
            )
            ||
            (
                !$ticket->getAgentTeamId() && !count(
                    \Orb\Util\Arrays::flatten(
                        array($this->evaluateExpression('agent.getTeamIds()'))
                    )
                )
            )
        );
        return $check;
    }

    public function func552c7c0fb700e(\Application\DeskPRO\Entity\Ticket $ticket)
    {
        $check = false;
        $check = ((in_array(
                $ticket->getStatusCode(),
                \Orb\Util\Arrays::flatten(array('awaiting_agent'))
            )) || (!$ticket->getStatusCode() && !count(\Orb\Util\Arrays::flatten(array('awaiting_agent')))));
        return $check;
    }

    public function mainCheck(\Application\DeskPRO\Entity\Ticket $ticket)
    {
        $check = false;
        if ($this->func552c7c0fad75f($ticket) && $this->func552c7c0fae0da($ticket) && $this->func552c7c0fb700e(
                $ticket
            )
        ) {
            $check = true;
        }
        return $check;
    }

    protected function evaluateExpression($expression)
    {
        return $this->expression_language
            ->evaluate($expression, array('agent' => $this->context->getAgent()));
    }
}