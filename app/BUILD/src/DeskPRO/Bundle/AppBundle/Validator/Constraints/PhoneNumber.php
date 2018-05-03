<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

/**
 * Class PhoneNumber.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class PhoneNumber extends AbstractNumber
{
    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
