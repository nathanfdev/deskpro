<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Voice;

use Symfony\Component\Validator\Constraint;

/**
 * Class VoiceAccount.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class VoiceAccount extends Constraint
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
