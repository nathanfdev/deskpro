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

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class SearchApplicationInstanceFilter
{
    /** @var string */
    private $scope;

    /** @var boolean */
    private $isDev;

    /** @var boolean */
    private $isInstalled;

    /** @var array */
    private $applicationIdList;

    /**
     * @param string|null $scope
     * @param string|null $applicationId
     */
    public function __construct($scope = null, $applicationId = null)
    {
        $this->scope = $scope;
        $this->applicationIdList = [];

        if (!is_null($applicationId)) {
            $this->applicationIdList[] = $applicationId;
        }
    }

    public function addApplicationId($applicationId)
    {
        $this->applicationIdList[] = $applicationId;
    }

    /**
     * @return bool
     */
    public function hasScope()
    {
        return !is_null($this->scope);
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getApplicationIdList()
    {
        return $this->applicationIdList;
    }

    /**
     * @return bool
     */
    public function hasApplicationIdList()
    {
        return !empty($this->applicationIdList);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return ! (
            $this->hasScope()
            || $this->hasApplicationIdList()
            || $this->hasIsInstalled()
            || $this->hasIsDev()
        );
    }

    /**
     * @return bool
     */
    public function hasIsInstalled()
    {
        return !is_null($this->isInstalled);
    }

    /**
     * @return bool
     */
    public function getIsInstalled()
    {
        return $this->isInstalled;
    }

    /**
     * @param bool $isInstalled
     */
    public function setIsInstalled( $isInstalled )
    {
        $this->isInstalled = (bool) $isInstalled;
    }

    /**
     * @return bool
     */
    public function hasIsDev()
    {
        return !is_null($this->isDev);
    }

    /**
     * @return bool
     */
    public function getIsDev()
    {
        return $this->isDev;
    }

    /**
     * @param bool $isDev
     */
    public function setIsDev( $isDev )
    {
        $this->isDev = (bool) $isDev;
    }
}
