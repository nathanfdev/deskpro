<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Department;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalDepartmentTermCompiler.
 */
class DbalDepartmentTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getEntityHelper()->buildQueryPart(
            'ticket.department_id',
            $term->getOp(),
            $term->getOption('department_ids')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
