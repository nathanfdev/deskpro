<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use DeskPRO\Bundle\AppBundle\Form\Error\ApiErrors;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidTermEngineTermValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof TermInterface) {
            $this->context->addViolationAt('options', ApiErrors::INVALID_INPUT);
        }

        if (!in_array($op = $value->getOp(), $supported = $value->getSupportedOps())) {
            $this->context->addViolationAt(
                'op',
                ValidTermEngineTerm::ERROR_OP_NOT_SUPPORTED,
                array('op' => $op, 'ops' => implode(', ', $supported))
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
