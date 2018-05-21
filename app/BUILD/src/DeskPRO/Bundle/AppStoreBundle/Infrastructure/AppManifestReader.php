<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppStoreBundle\Domain;

/**
 * Class AppManifestJsonReader.
 */
class AppManifestReader
{
    /**
     * Apply a list of transformations to update a manifest to a target version
     *
     * @todo use JMSSerializer for deserializing and create a simple transform model (transform class etc) to extend the functionality to other transform types
     * @param {array} $actualManifest
     * @return mixed
     */
    private function applyBackwardsCompatibleTransformation($actualManifest)
    {
        $transforms = [
            [ 'fromVersion' => '2.0.0', 'toVersion' => '2.1.0', 'type' => 'changeKey', 'fromKey' => 'external_apis', 'toKey' => 'externalApis' ],
            [ 'fromVersion' => '2.0.0', 'toVersion' => '2.1.0', 'type' => 'changeKey', 'fromKey' => 'is_single', 'toKey' => 'isSingle' ],
            [ 'fromVersion' => '2.0.0', 'toVersion' => '2.1.0', 'type' => 'changeKey', 'fromKey' => 'deskpro_api_tags', 'toKey' => 'deskproApiTags' ],
            [ 'fromVersion' => '2.1.0', 'toVersion' => '2.2.0', 'type' => 'changeKey', 'fromKey' => 'state', 'toKey' => 'storage' ],
            [ 'fromVersion' => '2.2.0', 'toVersion' => '2.3.0', 'type' => 'setKey', 'key' => 'settings', 'value' => [] ],
            [ 'fromVersion' => '2.3.0', 'toVersion' => '2.4.0', 'type' => 'changeKey', 'fromKey' => 'externalApis', 'toKey' => 'domainWhitelist' ],
        ];
        $transformedManifest = $actualManifest;

        do {
            $currentVersion = $transformedManifest['version'];

            $applicableTransforms = array_filter($transforms, function ($transform) use ($currentVersion) {
                return $transform['fromVersion'] === $currentVersion;
            });

            $versions = array_map(
                function ($transform) { return $transform['toVersion']; },
                $applicableTransforms
            );
            $versions = array_unique($versions);
            if (count($versions) > 1) {
                throw new \RuntimeException('Transformations between versions can only be applied one version pair at the time');
            }
            $targetVersion = current($versions);

            foreach ( $applicableTransforms as $transform ) {
                if ($transform['type'] === 'changeKey') {
                    $fromKey = $transform['fromKey'];
                    $toKey = $transform['toKey'];

                    if (array_key_exists($fromKey, $actualManifest)) {
                        $transformedManifest[$toKey] = $actualManifest[$fromKey];
                        unset($transformedManifest[$fromKey]);
                    }
                }

                if ($transform['type'] === 'setKey') {
                    $key = $transform['key'];
                    $value = $transform['value'];
                    $transformedManifest[$key] = $value;
                }
            }

            if (!empty($targetVersion)) {
                $transformedManifest['version'] = $targetVersion;
            }

        } while (count($applicableTransforms) > 0);

        return $transformedManifest;
    }

    /**
     * @param $jsonString
     * @return string|null
     */
    public function readVersionFromJson($jsonString)
    {
        $decoded = json_decode($jsonString, true);
        if (is_array($decoded) && array_key_exists('version', $decoded)) {
            $version = $decoded['version'];
            return is_string($version) && !empty($version) ? $version : null;
        }
        return null;
    }

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
        $transformedMap = $this->applyBackwardsCompatibleTransformation($manifestMap);
        // todo inject serializer
        /** @var \JMS\Serializer\Serializer $serializer */
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $manifest = $serializer->fromArray($transformedMap, Domain\AppManifest::class);
        return $manifest;
    }
}
