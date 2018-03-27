<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\App;

/**
 * Acts as a data structure used as an argument to the methods of the AppManipulator
 * when installing/updating/uninstalling app instances.
 */
class AppManipulatorContext
{
    /**
     * an array with the settings (keys are to be as defined in manifest.json).
     *
     * @var array|null
     */
    private $settings;

    /**
     * a new title to give the app instance, otherwise the app package name is used.
     *
     * @var string|null
     */
    private $inputTitle;

    /**
     * should be provided during an update manipulation.
     *
     * @var array|null
     */
    private $saveAssets;

    /**
     * if relevant (if app package is a usersource app) then this must be the
     * usersource interface the app applies to ("user" or "agent").
     *
     * @var string|null
     */
    private $usersourceType;

    public function __construct(array $settings, $inputTitle)
    {
        $this->settings   = $settings;
        $this->inputTitle = $inputTitle;
    }

    public function isUsersource()
    {
        return $this->usersourceType !== null;
    }

    /**
     * @return array|null
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return null|string
     */
    public function getInputTitle()
    {
        return $this->inputTitle;
    }

    /**
     * @param null|string $inputTitle
     */
    public function setInputTitle($inputTitle)
    {
        $this->inputTitle = $inputTitle;
    }

    /**
     * @return array|null
     */
    public function getSaveAssets()
    {
        return $this->saveAssets;
    }

    /**
     * @param array|null $saveAssets
     */
    public function setSaveAssets($saveAssets)
    {
        $this->saveAssets = $saveAssets;
    }

    /**
     * @return null|string
     */
    public function getUsersourceType()
    {
        return $this->usersourceType;
    }

    /**
     * @param null|string $usersourceType
     */
    public function setUsersourceType($usersourceType)
    {
        $this->usersourceType = $usersourceType;
    }
}
