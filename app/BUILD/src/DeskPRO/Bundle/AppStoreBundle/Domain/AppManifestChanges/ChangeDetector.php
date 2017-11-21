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
