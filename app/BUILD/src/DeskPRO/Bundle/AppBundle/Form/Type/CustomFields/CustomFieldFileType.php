<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use Symfony\Component\Form\AbstractType;

/**
 * Class CustomFieldFileType.
 */
class CustomFieldFileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BlobAuthType::class;
    }
}
