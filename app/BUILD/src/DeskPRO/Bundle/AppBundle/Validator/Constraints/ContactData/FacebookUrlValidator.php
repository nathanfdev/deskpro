<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\ContactData;

use DeskPRO\Component\Util\RegexUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class FacebookUrlValidator.
 */
class FacebookUrlValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FacebookUrl) {
            throw new UnexpectedTypeException($constraint, FacebookUrl::class);
        }

        if (!$value) {
            return;
        }
        if (!is_scalar($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $patterns = [
            '#/profile\.php?id=([0-9]+)#',
            '#facebook\.com/([a-zA-Z0-9\.\-_]+)#',
            '#facebook\.com/people/([a-zA-Z0-9\.\-_]+)#',
        ];

        foreach ($patterns as $pattern) {
            if (RegexUtils::safePregMatch($pattern, $value)) {
                return;
            }
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;
        $context
            ->buildViolation($constraint->message)
            ->setCode(FacebookUrl::NOT_PROFILE_URL)
            ->addViolation()
        ;
    }
}
