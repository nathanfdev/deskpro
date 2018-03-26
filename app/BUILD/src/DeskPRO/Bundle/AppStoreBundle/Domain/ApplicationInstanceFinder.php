<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface ApplicationInstanceFinder
{
    /**
     * @return ApplicationInstance[]
     */
    function findAll();

    /**
     * @param SearchApplicationInstanceFilter $filter
     * @return ApplicationInstance[]
     */
    function findByFilter(SearchApplicationInstanceFilter $filter);

    /**
     * @param $applicationName
     * @return ApplicationInstance
     */
    function findSoleApplicationInstance($applicationName);

    /**
     * @param $id
     * @return ApplicationInstance
     */
    function findById($id);
}

