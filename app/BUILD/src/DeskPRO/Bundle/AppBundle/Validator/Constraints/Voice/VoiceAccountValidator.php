<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount as VoiceAccountEntity;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class VoiceAccountValidator.
 */
class VoiceAccountValidator extends ConstraintValidator
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * Constructor.
     *
     * @param TwilioAdapter $twilioAdapter
     */
    public function __construct(TwilioAdapter $twilioAdapter)
    {
        $this->twilioAdapter = $twilioAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof VoiceAccount) {
            throw new UnexpectedTypeException($constraint, VoiceAccount::class);
        }

        if (!$value instanceof VoiceAccountEntity) {
            throw new UnexpectedTypeException($value, VoiceAccountEntity::class);
        }

        // don't check if not all credentials were provided
        if (!$value->getAccountSid() || !$value->getAuthToken()) {
            return;
        }

        if (!$this->twilioAdapter->getAccount($value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(VoiceAccount::INVALID_ACCOUNT_CREDENTIALS)
                ->addViolation()
            ;
        }
    }
}
