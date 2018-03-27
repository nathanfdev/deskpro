<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpAgentTermCompiler.
 */
class PhpAgentTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $ids = $term->getOption('agent_ids');

        $check_me  = false;
        $check_ids = [];
        foreach ($ids as $id) {
            if ($id === AgentTerm::ID_ME) {
                $check_me = 'agent.getId()';
            } else {
                $check_ids[] = (int) $id;
            }
        }

        $check = 'check_contains(ticket.getAgentId(), :op, :ids';
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
