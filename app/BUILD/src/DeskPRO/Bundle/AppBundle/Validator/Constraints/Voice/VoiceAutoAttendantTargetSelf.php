<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Voice;

use Symfony\Component\Validator\Constraint;

/**
 * Class VoiceAutoAttendantTargetSelf.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class VoiceAutoAttendantTargetSelf extends Constraint
{
    const TARGET_SELF = 'voice_target_self';

    public $message = 'Unable to target self.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
