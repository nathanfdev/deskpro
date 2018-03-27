<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpProblemTermCompiler.
 */
class PhpProblemTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return new PhpCheck('check_contains(:problem, :op, ticket.getProblemIds())', [
            'op'      => $term->getOp(),
            'problem' => $term->getOption('problem'),
        ]);
    }
}
