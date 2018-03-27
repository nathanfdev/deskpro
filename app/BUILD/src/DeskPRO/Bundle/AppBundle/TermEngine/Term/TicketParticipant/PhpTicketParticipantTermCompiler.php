<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketParticipantTermCompiler.
 */
class PhpTicketParticipantTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $ids = $term->getOption('person_ids');

        $use_me  = false;
        $use_ids = [];
        foreach ($ids as $id) {
            if ($id == TicketParticipantTerm::ID_ME) {
                $use_me = 'agent.getId()';
            } else {
                $use_ids[] = (int) $id;
            }
        }

        if ($this->isOp($op, TermInterface::OP_IS)) {
            $prefix = '';
        } else {
            $prefix = 'not ';
        }

        if ($use_me && count($use_ids)) {
            $check = new PhpCheck($prefix.'ticket.hasAnyParticipantId([:ids, agent.getId()])');
            $check->setVariable('ids', $use_ids);
        } elseif ($use_me) {
            $check = new PhpCheck($prefix.'ticket.hasAnyParticipantId([agent.getId()])');
        } else {
            $check = new PhpCheck($prefix.'ticket.hasAnyParticipantId(:ids)');
            $check->setVariable('ids', $use_ids);
        }

        return $check;
    }
}
