<?php

namespace DeskPRO\Bundle\VoiceBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount as VoiceAccountEntity;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PlivoVoiceAccountValidator.
 */
class PlivoVoiceAccountValidator extends ConstraintValidator
{
    /**
     * @var PlivoAdapter
     */
    private $plivoAdapter;

    /**
     * Constructor.
     *
     * @param PlivoAdapter $plivoAdapter
     */
    public function __construct(PlivoAdapter $plivoAdapter)
    {
        $this->plivoAdapter = $plivoAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PlivoVoiceAccount) {
            throw new UnexpectedTypeException($constraint, TwilioVoiceAccount::class);
        }

        if (!$value instanceof VoiceAccountEntity) {
            throw new UnexpectedTypeException($value, VoiceAccountEntity::class);
        }

        // don't check if not all credentials were provided
        if (!$value->getAccountId() || !$value->getAuthToken()) {
            return;
        }

        if (!$this->plivoAdapter->getAccount($value)) {
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
