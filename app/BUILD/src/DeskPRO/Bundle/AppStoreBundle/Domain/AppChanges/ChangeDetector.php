<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppChanges;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppVersion;

class ChangeDetector
{


    /**
     * Checks the two manifests for a difference that would cause a change for the forceConfiguration status
     *
     * @param AppManifest $current
     * @param AppManifest $previous
     * @return BooleanFlagChange|null
     */
    public function forceConfigurationStatusChange( AppManifest $current, AppManifest $previous)
    {
        $currentVersion = AppVersion::parse($current->getAppVersion());
        $previousVersion = AppVersion::parse($previous->getAppVersion());

        // no longer a pre-release version
        if (!empty($previousVersion->getLabel()) && empty($currentVersion->getLabel())) {
            $versionChange = true;
        } else {
            $versionChange = $currentVersion->getMajor() !== $previousVersion->getMajor() || $currentVersion->getMinor() !== $previousVersion->getMinor();
        }

        if ($versionChange && count($current->getSettings())) {
            return new BooleanFlagChange('forceConfiguration', 'update', true);
        }

        return null;
    }

    /**
     * @param AppManifest $current
     * @param AppManifest $previous
     *
     * @return array|GenericChange[]
     */
    public function settingsChanges(AppManifest $current, AppManifest $previous)
    {
        $previousValues = array_map(
            function (AppManifest\Setting $setting) {
                return $setting->getName();
            },
            $previous->getSettings()
        );
        $currentValues = array_map(
            function (AppManifest\Setting $setting) {
                return $setting->getName();
            },
            $current->getSettings()
        );

        $deletions = array_diff($previousValues, $currentValues);
        $additions = array_diff($currentValues, $previousValues);

        $changes = [];
        /** @var AppManifest\Setting $value */
        foreach ($previous->getSettings() as $value) {
            if (in_array($value->getName(), $deletions)) {
                $changes[] = new SettingChange('delete', null, $value);
            }
        }

        /* @var AppManifest\CustomField $customField */
        foreach ($current->getSettings() as $value) {
            if (in_array($value->getName(), $additions)) {
                $changes[] = new SettingChange('add', $value);
            }
        }

        return $changes;
    }

    /**
     * @param AppManifest $current
     * @param AppManifest $previous
     *
     * @return array|CustomFieldChange
     */
    public function customFieldChanges(AppManifest $current, AppManifest $previous)
    {
        $previousValues = array_map(
            function (AppManifest\CustomField $customField) {
                return $customField->getAlias();
            },
            $previous->getCustomFields()
        );
        $currentValues = array_map(
            function (AppManifest\CustomField $customField) {
                return $customField->getAlias();
            },
            $current->getCustomFields()
        );

        $deletions = array_diff($previousValues, $currentValues);
        $additions = array_diff($currentValues, $previousValues);

        $changes = [];
        /** @var AppManifest\CustomField $value */
        foreach ($previous->getCustomFields() as $value) {
            if (in_array($value->getAlias(), $deletions)) {
                $changes[] = new CustomFieldChange('delete', null, $value);
            }
        }

        /* @var AppManifest\CustomField $customField */
        foreach ($current->getCustomFields() as $value) {
            if (in_array($value->getAlias(), $additions)) {
                $changes[] = new CustomFieldChange('add', $value);
            }
        }

        return $changes;
    }
}
