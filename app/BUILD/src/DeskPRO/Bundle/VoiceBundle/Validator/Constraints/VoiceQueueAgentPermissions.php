<?php

namespace DeskPRO\Bundle\VoiceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class VoiceQueueAgentPermissions.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class VoiceQueueAgentPermissions extends Constraint
{
    const NO_DEPARTMENT_PERMISSION = 'voice_no_department_permission';

    public $message = 'Agent {{agent}} has no permission for department {{department}}';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
