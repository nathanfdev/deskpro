<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\CustomFieldData;
use Doctrine\Common\Collections\Collection;

/**
 * Interface CustomPerDataOwnerInterface.
 */
interface CustomPerDataOwnerInterface extends EntityInterface
{
    /**
     * @return CustomFieldData[]|Collection
     */
    public function getCustomPerData();

    /**
     * @param CustomFieldData[]|Collection $customPerData
     */
    public function setCustomPerData($customPerData);
}
