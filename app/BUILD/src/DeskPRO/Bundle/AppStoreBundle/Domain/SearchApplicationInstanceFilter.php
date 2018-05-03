<?php

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
