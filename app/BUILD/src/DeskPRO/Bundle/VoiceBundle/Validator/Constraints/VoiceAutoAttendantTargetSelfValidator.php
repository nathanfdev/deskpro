<?php

namespace DeskPRO\Bundle\VoiceBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendantDialNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class VoiceAutoAttendantTargetSelfValidator.
 */
class VoiceAutoAttendantTargetSelfValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof VoiceAutoAttendantTargetSelf) {
            throw new UnexpectedTypeException($constraint, VoiceAutoAttendantTargetSelf::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof VoiceAutoAttendantDialNumber) {
            throw new UnexpectedTypeException($constraint, VoiceAutoAttendantDialNumber::class);
        }

        $target = $value->getTarget();
        if (!$target instanceof VoiceAutoAttendantTarget) {
            return;
        }

        if ($target->getAutoAttendant() === $value->getVoiceAutoAttendant()) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(VoiceAutoAttendantTargetSelf::TARGET_SELF)
                ->addViolation()
            ;
        }
    }
}
