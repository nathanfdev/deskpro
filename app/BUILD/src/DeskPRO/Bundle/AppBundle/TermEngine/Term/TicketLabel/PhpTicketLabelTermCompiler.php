<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketLabelTermCompiler.
 */
class PhpTicketLabelTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op    = $term->getOp();
        $label = $term->getOption('label');

        $real_op = '!=';
        if (TermInterface::OP_NOT_HAS === $op) {
            $real_op = '==';
        }

        return new PhpCheck(
            sprintf('ticket.findLabelByString(:label) %s null', $real_op),
            [
                'label' => $label,
            ]
        );
    }
}
