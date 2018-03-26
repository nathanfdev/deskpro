<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class SearchFilterValueArrayMap implements SearchAssetFilterOptions, SearchAppStorageFilterOptions, SearchSettingsFilterOptions
{
    /** @var string */
    private $applicationId;

    /** @var string */
    private $filePathPattern;

    /** @var string */
    private $fileExtension;

    /** @var string */
    private $entityId;

    /** @var string */
    private $stateVariableName;

    /** @var string */
    private $showPrivateSettings;

    /**
     * @return string
     */
    public function getFilePathPattern()
    {
        return $this->filePathPattern;
    }

    /**
     * @param string $filePathPattern
     */
    public function setFilePathPattern($filePathPattern)
    {
        $this->filePathPattern = $filePathPattern;
    }

    /**
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * @param string $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $id
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    }

    /**
     * @param string $scopeList
     */
    public function setStateVariableScope($scopeList)
    {
        $this->entityId = $scopeList;
    }

    /**
     * @return string
     */
    public function getStateVariableName()
    {
        return $this->stateVariableName;
    }

    /**
     * @param string $stateVariableName
     */
    public function setStateVariableName($stateVariableName)
    {
        $this->stateVariableName = $stateVariableName;
    }

    /**
     * @return string
     */
    public function getShowPrivateSettings()
    {
        return $this->showPrivateSettings;
    }

    /**
     * @param string $showPrivateSettings
     */
    public function setShowPrivateSettings($showPrivateSettings)
    {
        $this->showPrivateSettings = $showPrivateSettings;
    }

    /**
     * @return string
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * @param string $applicationId
     */
    public function setApplicationId($applicationId)
    {
        $this->applicationId = $applicationId;
    }
}
