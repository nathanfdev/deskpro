<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

/**
 * Class CustomData.
 */
class CustomData extends AbstractCustomDefConstraint
{
    const TARGET_COLLECTION = 'collection';
    const TARGET_FIELD      = 'field';

    public $target = self::TARGET_COLLECTION;
}
