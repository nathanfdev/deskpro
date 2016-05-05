<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class CustomDataValidator.
 */
class CustomDataValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CustomData) {
            throw new UnexpectedTypeException($constraint, CustomData::class);
        }
        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        $custom_def = $constraint->custom_def;
        if (!$custom_def instanceof CustomDefAbstract) {
            throw new UnexpectedTypeException($custom_def, CustomDefAbstract::class);
        }

        $validators      = [];
        $handler_options = [
            'custom_def' => $custom_def,
            'context'    => $constraint->context,
        ];

        switch ($custom_def->getType()) {
            case CustomDefAbstract::TYPE_TEXT:
            case CustomDefAbstract::TYPE_TEXTAREA:
            case CustomDefAbstract::TYPE_HIDDEN:
                $validators[] = new AppAssert\CustomField\Text($handler_options);
                break;
            case CustomDefAbstract::TYPE_TOGGLE:
                $validators[] = new AppAssert\CustomField\Toggle($handler_options);
                break;
            case CustomDefAbstract::TYPE_DATE:
                $validators[] = new AppAssert\CustomField\Date($handler_options);
                break;
            case CustomDefAbstract::TYPE_DATETIME:
                $validators[] = new AppAssert\CustomField\DateTime($handler_options);
                break;
            case CustomDefAbstract::TYPE_CHOICE:
                $validators[] = new AppAssert\CustomField\Choice($handler_options);
                break;
        }

        /** @var Collection $custom_def_data */
        $custom_def_data = $value->filter(function (CustomDataAbstract $custom_data) use ($custom_def) {
            return $custom_data->root_field === $custom_def;
        });

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context   = $this->context;
        $validator = $context->getValidator()->inContext($context);

        if ($constraint->target === CustomData::TARGET_COLLECTION) {
            $validator->atPath('['.$custom_def->getId().']');
        }

        $validator->validate($custom_def_data, $validators);
    }
}
