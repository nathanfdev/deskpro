<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface ApplicationInstance
{
    /**
     * Returns the system identifier for the application
     *
     * @return string
     */
    function getApplicationId();

    /**
     * Returns the system identifier assigned to the instance
     *
     * @return string
     */
    function getId();

    /**
     * @return string
     */
    function getScope();

    /**
     * @return string
     */
    function getSettings();
}
