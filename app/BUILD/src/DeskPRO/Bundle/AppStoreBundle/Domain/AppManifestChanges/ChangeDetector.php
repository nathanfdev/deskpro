<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestChanges;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

class ChangeDetector
{
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
                $changes[] = new SettingChange('delete', $value);
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
                $changes[] = new CustomFieldChange('delete', $value);
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
