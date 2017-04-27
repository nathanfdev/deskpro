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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppStoreBundle\Domain;

class AppManifestJsonReader
{
    /**
     * @param $jsonString
     *
     * @return Domain\AppManifest
     */
    public function readManifest($jsonString)
    {
        $manifestMap = json_decode($jsonString, true);
        $manifest    = new Domain\AppManifest();

        $this->mapManifestMapToObject($manifestMap, $manifest);

        return $manifest;
    }

    private function mapManifestMapToObject(array $manifestMap, Domain\AppManifest $manifest)
    {
        // temp get serializer from static container
        // temp manifest setters
        // todo refactor
        $serializer = App::$container->get('serializer');

        $manifest->setName($manifestMap['name']);
        $manifest->setDescription($manifestMap['description']);
        $manifest->setScope($manifestMap['scope']);
        $manifest->setAuthor($serializer->fromArray($manifestMap['author'], Domain\AppManifestAuthor::class));
        $manifest->setSettings($serializer->fromArray($manifestMap['settings'], 'array<'.Domain\AppManifestSetting::class.'>'));

        $value           = $manifestMap['settings'];
        $defaultSettings = [];
        foreach ($value as $setting) {
            if (
                is_array($setting)
                && array_key_exists('default_value', $setting)
                && array_key_exists('name', $setting)
            ) {
                $defaultSettings[$setting['name']] = $setting['default_value'];
            }
        }
        $manifest->setDefaultSettings($defaultSettings);
    }
}
