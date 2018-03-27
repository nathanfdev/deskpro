<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface SearchAppStorageFilterOptions
{
    /**
     * @return string
     */
    public function getApplicationId();

    /**
     * @return string
     */
    public function getEntityId();

    /**
     * @return string
     */
    public function getStateVariableName();
}
