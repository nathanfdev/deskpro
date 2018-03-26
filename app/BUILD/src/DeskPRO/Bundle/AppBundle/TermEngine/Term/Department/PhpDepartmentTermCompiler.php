<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Department;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpDepartmentTermCompiler.
 */
class PhpDepartmentTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $ids = $term->getOption('department_ids');

        return new PhpCheck(
            'check_contains(ticket.getDepartmentId(), :op, :ids)',
            [
                'op'  => $op,
                'ids' => $ids,
            ]
        );
    }
}
