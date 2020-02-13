<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person;

use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\Security\LimitEmailDomainsChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class LimitEmailDomainsValidator.
 */
class LimitEmailDomainsValidator extends ConstraintValidator
{
    /**
     * @var LimitEmailDomainsChecker
     */
    private $domainsChecker;

    /**
     * Constructor.
     *
     * @param LimitEmailDomainsChecker $domainsChecker
     */
    public function __construct(LimitEmailDomainsChecker $domainsChecker)
    {
        $this->domainsChecker = $domainsChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LimitEmailDomains) {
            throw new UnexpectedTypeException($constraint, LimitEmailDomains::class);
        }

        if ($value instanceof PersonEmail) {
            $value = $value->getEmail();
        }

        if (!$this->domainsChecker->checkEmail($value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(LimitEmailDomains::BAD_EMAIL_DOMAIN)
                ->addViolation()
            ;
        }
    }
}
