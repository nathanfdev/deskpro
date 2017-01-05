<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
