<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpAgentTeamTermCompiler.
 */
class PhpAgentTeamTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $ids = $term->getOption('agent_team_ids');

        $check_me  = false;
        $check_ids = [];
        foreach ($ids as $id) {
            if ($id === AgentTeamTerm::TEAM_ID_ME) {
                $check_me = 'agent.getTeamIds()';
            } else {
                $check_ids[] = (int) $id;
            }
        }

        $check = 'check_contains(ticket.getAgentTeamId(), :op, :ids';
        if ($check_me) {
            $check .= sprintf(', %s', $check_me);
        }
        $check .= ')';

        return new PhpCheck(
            $check,
            [
                'op'  => $op,
                'ids' => $check_ids,
            ]
        );
    }
}
