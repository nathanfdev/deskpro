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

/**
 * Class AppManifestJsonReader.
 */
class AppManifestJsonReader
{
    /**
     * @param string $jsonString
     *
     * @return Domain\AppManifest
     */
    public function readManifestFromJson($jsonString)
    {
        return $this->readManifestFromArray(json_decode($jsonString, true));
    }

    /**
     * @param array $manifestMap
     *
     * @return Domain\AppManifest
     */
    public function readManifestFromArray(array $manifestMap)
    {
        // temp get serializer from static container
        // temp manifest setters
        // todo refactor
        $serializer = App::$container->get('serializer');

        $manifest = new Domain\AppManifest();
        $manifest
            ->setVersion($manifestMap['appVersion'])
            ->setName($manifestMap['name'])
            ->setTitle($manifestMap['title'])
            ->setDescription($manifestMap['description'])
            ->setScope($manifestMap['scope'])
            ->setAuthor($serializer->fromArray($manifestMap['author'], Domain\AppManifestAuthor::class))
            ->setSettings($serializer->fromArray($manifestMap['settings'], 'array<'.Domain\AppManifestSetting::class.'>'))
            ->setExternalApis($manifestMap['external_apis'])
        ;

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

        return $manifest;
    }
}
