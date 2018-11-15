<?php

namespace DeskPRO\Bundle\VoiceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class TwilioVoiceAccount.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class TwilioVoiceAccount extends Constraint
{
    const INVALID_ACCOUNT_CREDENTIALS = 'invalid_account_credentials';

    public $message = 'Unable to connect to Twilio account.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
