<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ValidTermEngineTermValidator.
 */
class ValidTermEngineTermValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof TermInterface) {
            $this->context->addViolationAt('options', ErrorsCodes::INVALID_INPUT);
        }

        if (!in_array($op = $value->getOp(), $supported = $value->getSupportedOps())) {
            $this->context->addViolationAt(
                'op',
                ValidTermEngineTerm::ERROR_OP_NOT_SUPPORTED,
                ['op' => $op, 'ops' => implode(', ', $supported)]
            );
        }

        $options = $value->getOptions();

        if (!is_array($options)) {
            throw new TransformationFailedException();
        }

        $options_resolver = $value::getOptionsResolver();

        foreach ($options_resolver->getConstraints() as $option => $constraints) {
            if (array_key_exists($option, $options)) {
                $this->context->validateValue($options[$option], $constraints, sprintf('options[%s]', $option));
            } else {
                throw new UnexpectedTypeException($options, sprintf('key [%s] missing', $option));
            }
        }
    }
}
