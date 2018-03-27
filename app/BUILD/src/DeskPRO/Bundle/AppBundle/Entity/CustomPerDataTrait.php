<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\CustomFieldData;
use Doctrine\Common\Collections\Collection;

/**
 * Class CustomPerDataTrait.
 */
trait CustomPerDataTrait
{
    /**
     * @var CustomFieldData[]|Collection
     */
    protected $customPerData;

    /**
     * {@inheritdoc}
     */
    public function getCustomPerData()
    {
        return $this->customPerData;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomPerData($customPerData)
    {
        $this->customPerData = $customPerData;
    }
}
