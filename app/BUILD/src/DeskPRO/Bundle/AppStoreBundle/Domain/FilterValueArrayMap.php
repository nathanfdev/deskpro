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

class FilterValueArrayMap implements AssetFilterValueMap, StateFilterValueMap, SettingsFilterValueMap
{
    /** @var string */
    private $filePathPattern;

    /** @var string */
    private $fileExtension;

    /** @var string */
    private $scopeList;

    /** @var string */
    private $stateVariableName;

    /** @var string */
    private $showPrivateSettings;

    /**
     * @return string
     */
    public function getFilePathPattern(): string
    {
        return $this->filePathPattern;
    }

    /**
     * @param string $filePathPattern
     */
    public function setFilePathPattern(string $filePathPattern)
    {
        $this->filePathPattern = $filePathPattern;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    /**
     * @param string $fileExtension
     */
    public function setFileExtension(string $fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return string
     */
    public function getScopeList(): string
    {
        return $this->scopeList;
    }

    /**
     * @param string $scopeList
     */
    public function setStateVariableScope(string $scopeList)
    {
        $this->scopeList = $scopeList;
    }

    /**
     * @return string
     */
    public function getStateVariableName(): string
    {
        return $this->stateVariableName;
    }

    /**
     * @param string $stateVariableName
     */
    public function setStateVariableName(string $stateVariableName)
    {
        $this->stateVariableName = $stateVariableName;
    }

    /**
     * @return string
     */
    public function getShowPrivateSettings(): string
    {
        return $this->showPrivateSettings;
    }

    /**
     * @param string $showPrivateSettings
     */
    public function setShowPrivateSettings(string $showPrivateSettings)
    {
        $this->showPrivateSettings = $showPrivateSettings;
    }

}
