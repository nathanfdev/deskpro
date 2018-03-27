<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpOrganizationTermCompiler.
 */
class PhpOrganizationTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return new PhpCheck('check_contains(ticket.getOrganization().getId(), :op, :organization)', [
            'op'           => $term->getOp(),
            'organization' => $term->getOption('organization'),
        ]);
    }
}
