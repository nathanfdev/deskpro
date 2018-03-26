<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Person;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpPersonTermCompiler.
 */
class PhpPersonTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return new PhpCheck('check_contains(ticket.getPersonId(), :op, :ids)', [
            'op'  => $term->getOp(),
            'ids' => $term->getOption('person_ids'),
        ]);
    }
}
