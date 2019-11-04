<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class EnoughApproversValidator.
 */
class EnoughApproversValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EnoughApprovers) {
            throw new UnexpectedTypeException($constraint, EnoughApprovers::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof AbstractBaseApproval) {
            throw new UnexpectedTypeException($value, AbstractBaseApproval::class);
        }

        // Check that we have enough approvers
        $count = $value->getApprovers()->count();
        if ($count < max($value->getRequiredApprovals(), $value->getRequiredRejections())) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(EnoughApprovers::NOT_ENOUGH_APPROVERS)
                ->addViolation()
            ;
        }
    }
}
