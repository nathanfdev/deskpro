<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalOrganizationTermCompiler.
 */
class DbalOrganizationTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getEntityHelper()->buildQueryPart(
            'ticket.organization_id',
            $term->getOp(),
            $term->getOption('organization')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
