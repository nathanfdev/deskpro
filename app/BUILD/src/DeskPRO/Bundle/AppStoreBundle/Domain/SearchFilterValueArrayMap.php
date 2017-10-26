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
