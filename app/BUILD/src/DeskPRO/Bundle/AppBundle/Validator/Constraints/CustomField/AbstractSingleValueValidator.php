<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Doctrine\Common\Collections\Collection;

/**
 * Class AbstractSingleValueValidator.
 */
abstract class AbstractSingleValueValidator extends AbstractCustomDefConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getData(Collection $value, AbstractCustomDefConstraint $constraint)
    {
        $custom_data = $value->first();

        return $custom_data ? $this->getCustomDataValue($custom_data) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDataValue(CustomDataAbstract $customData)
    {
        return $customData->getData();
    }
}
