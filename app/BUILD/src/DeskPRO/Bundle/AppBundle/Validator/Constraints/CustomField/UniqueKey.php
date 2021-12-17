<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

/**
 * Custom field unique key constraint
 */
class UniqueKey extends AbstractCustomDefConstraint
{
    const DUPE_UNIQUE_KEY = 'dupe_unique_key';

    /**
     * {@inheritdoc}
     */
    public $message = 'External Unique Key "{{ key }}" already exists.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'unique_key_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode()
    {
        return self::DUPE_UNIQUE_KEY;
    }
}
