<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\ContactData;

use DeskPRO\Component\Util\RegexUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class LinkedInValidator.
 */
class LinkedInUrlValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LinkedInUrl) {
            throw new UnexpectedTypeException($constraint, LinkedInUrl::class);
        }

        if (!$value) {
            return;
        }
        if (!is_scalar($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!RegexUtils::safePregMatch('#/in/(.*?)$#', $value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(LinkedInUrl::NOT_PROFILE_URL)
                ->addViolation()
            ;
        }
    }
}
