<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class AbstractCustomDefConstraintValidator.
 */
abstract class AbstractCustomDefConstraintValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AbstractCustomDefConstraint) {
            throw new UnexpectedTypeException($constraint, AbstractCustomDefConstraint::class);
        }
        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        $custom_def = $constraint->custom_def;
        if (!$custom_def instanceof CustomDefAbstract) {
            throw new UnexpectedTypeException($custom_def, CustomDefAbstract::class);
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context = $this->context;

        $data       = $this->getData($value, $constraint);
        $validators = $this->getValidators($data, $constraint);

        $validator = $context->getValidator()->inContext($context);
        $validator->validate($data, $validators);
    }

    /**
     * @param mixed                       $data
     * @param AbstractCustomDefConstraint $constraint
     *
     * @return array
     */
    abstract protected function getValidators($data, AbstractCustomDefConstraint $constraint);

    /**
     * @param Collection                  $value
     * @param AbstractCustomDefConstraint $constraint
     *
     * @return mixed
     */
    abstract protected function getData(Collection $value, AbstractCustomDefConstraint $constraint);
}
