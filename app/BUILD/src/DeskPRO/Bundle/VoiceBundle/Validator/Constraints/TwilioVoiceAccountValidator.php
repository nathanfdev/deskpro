<?php

namespace DeskPRO\Bundle\VoiceBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount as VoiceAccountEntity;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TwilioVoiceAccountValidator.
 */
class TwilioVoiceAccountValidator extends ConstraintValidator
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
        if (!$constraint instanceof TwilioVoiceAccount) {
            throw new UnexpectedTypeException($constraint, TwilioVoiceAccount::class);
        }

        if (!$value instanceof VoiceAccountEntity) {
            throw new UnexpectedTypeException($value, VoiceAccountEntity::class);
        }

        // don't check if not all credentials were provided
        if (!$value->getAccountId() || !$value->getAuthToken()) {
            return;
        }

        if (!$this->twilioAdapter->getAccount($value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(TwilioVoiceAccount::INVALID_ACCOUNT_CREDENTIALS)
                ->addViolation()
            ;
        }
    }
}
