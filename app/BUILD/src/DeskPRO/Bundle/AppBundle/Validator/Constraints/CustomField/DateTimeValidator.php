<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DateTimeValidator.
 */
class DateTimeValidator extends AbstractDateTimeValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getFormatValidator()
    {
        return new Assert\DateTime();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormat()
    {
        return 'Y-m-d H:i:s';
    }
}
