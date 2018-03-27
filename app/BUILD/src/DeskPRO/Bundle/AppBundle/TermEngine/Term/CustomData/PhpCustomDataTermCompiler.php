<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpCustomDataTermCompiler.
 */
class PhpCustomDataTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return new PhpCheck('ticket.isCustomFieldEqualTo(:field_id, :custom_data_value)', [
            'field_id'          => $term->getOption('field_id'),
            'custom_data_value' => $term->getOption('custom_data_value'),
        ]);
    }
}
