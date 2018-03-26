<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Voice;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class VoiceExtensionValidator.
 */
class VoiceExtensionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof VoiceExtension) {
            throw new UnexpectedTypeException($constraint, VoiceExtension::class);
        }

        if (!$value) {
            return;
        }
        if (!is_scalar($value)) {
            throw new UnexpectedTypeException($value, 'scalar');
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context   = $this->context;
        $validator = $context->getValidator()->inContext($context);
        $validator->validate($value, [
            new Assert\GreaterThanOrEqual(['value' => 1001]),
            new Assert\LessThanOrEqual(['value' => 9999]),
        ]);
    }
}
