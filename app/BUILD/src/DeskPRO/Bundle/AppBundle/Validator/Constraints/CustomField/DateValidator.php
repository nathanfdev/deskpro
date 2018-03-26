<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DateValidator.
 */
class DateValidator extends AbstractDateTimeValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getFormatValidator()
    {
        return new Assert\Date();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormat()
    {
        return 'Y-m-d';
    }
}
