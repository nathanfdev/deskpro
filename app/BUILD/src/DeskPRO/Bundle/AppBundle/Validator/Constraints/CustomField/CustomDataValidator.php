<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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

        $customDef = $constraint->custom_def;
        if (!$customDef instanceof CustomDefAbstract) {
            throw new UnexpectedTypeException($customDef, CustomDefAbstract::class);
        }

        $validators     = [];
        $handlerOptions = [
            'custom_def' => $customDef,
            'context'    => $constraint->context,
        ];

        switch ($customDef->getType()) {
            case CustomDefAbstract::TYPE_TEXT:
            case CustomDefAbstract::TYPE_TEXTAREA:
            case CustomDefAbstract::TYPE_HIDDEN:
                $validators[] = new AppAssert\CustomField\Text($handlerOptions);
                break;
            case CustomDefAbstract::TYPE_TOGGLE:
                $validators[] = new AppAssert\CustomField\Toggle($handlerOptions);
                break;
            case CustomDefAbstract::TYPE_DATE:
                $validators[] = new AppAssert\CustomField\Date($handlerOptions);
                break;
            case CustomDefAbstract::TYPE_DATETIME:
                $validators[] = new AppAssert\CustomField\DateTime($handlerOptions);
                break;
            case CustomDefAbstract::TYPE_CHOICE:
                $validators[] = new AppAssert\CustomField\Choice($handlerOptions);
                break;
            case CustomDefAbstract::TYPE_URL:
                $validators[] = new AppAssert\CustomField\Url($handlerOptions);
                break;
            case CustomDefAbstract::TYPE_CURRENCY:
                $validators[] = new AppAssert\CustomField\Currency($handlerOptions);
                break;
            case CustomDefAbstract::TYPE_FILE:
                $validators[] = new AppAssert\CustomField\File($handlerOptions);
                break;
        }

        /** @var Collection $customDefData */
        $customDefData = $value->filter(function (CustomDataAbstract $custom_data) use ($customDef) {
            return $custom_data->getRootField() === $customDef;
        });

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context   = $this->context;
        $validator = $context->getValidator()->inContext($context);

        if ($constraint->target === CustomData::TARGET_COLLECTION) {
            $validator->atPath('['.$customDef->getId().']');
        }

        $validator->validate($customDefData, $validators);
    }
}
