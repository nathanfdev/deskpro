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

use DeskPRO\Bundle\AppStoreBundle\Domain;
use JsonSchema;

class AppBundleValidator implements Domain\AppBundleValidator
{
    /** @var ManifestSchemaLocator */
    private $schemaLocator;

    /** @var JsonSchema\Validator */
    private $schemaValidator;

    /**
     * ApplicationService constructor.
     * @param ManifestSchemaLocator $schemaLocator
     */
    public function __construct(JsonSchema\Validator $schemaValidator, ManifestSchemaLocator $schemaLocator)
    {
        $this->schemaLocator = $schemaLocator;
        $this->schemaValidator = $schemaValidator;
    }

    public function validateBundle(Domain\AppBundle $bundle)
    {
        try {
            $schema = $this->resolveManifestSchema($bundle);
        } catch (\Exception $e) { // should log perhaps
            return false;
        }

        $manifestData = $this->readManifest($bundle);
        if ($manifestData instanceof \stdClass) {
            $this->schemaValidator->validate($manifestData, (object)$schema);
            return $this->schemaValidator->isValid();
        }
        return false;
    }

    /**
     * @param Domain\AppBundle $bundle
     * @return \stdClass|null
     */
    private function readManifest(Domain\AppBundle $bundle)
    {
        $manifestString = $bundle->getManifestAsString();

        // check if this manifest can be deserialized into the current manifest object
        try {
            $manifestReader = new AppManifestReader();
            $manifestReader->readManifestFromJson($manifestString);
        } catch (\Exception $e) {
            return null;
        }

        return json_decode($manifestString);
    }

    /**
     * @param Domain\AppBundle $bundle
     * @return \stdClass
     * @throws \RuntimeException
     */
    private function resolveManifestSchema(Domain\AppBundle $bundle)
    {
        $manifestString = $bundle->getManifestAsString();
        $manifestReader = new AppManifestReader();
        $manifestVersion = $manifestReader->readVersionFromJson($manifestString);

        if (empty($manifestVersion)) {
            throw new \RuntimeException(sprintf('failed to read manifest version from app bundle: %s', $manifestVersion));
        }

        $schemaFile = $this->schemaLocator->locate($manifestVersion);
        if (! $schemaFile instanceof \SplFileInfo) {
            throw new \RuntimeException(sprintf('could not locate the schema for manifest version: %s', $manifestVersion));
        }

        $filePath = $schemaFile->getRealPath();
        $contents = file_get_contents($filePath);
        if (false === $contents) {
            throw new \RuntimeException('could not read contents of app manifest schema file from: ' .$filePath);
        }

        $contents = json_decode($contents);
        if ($contents instanceof \stdClass) {
            return $contents;
        }

        throw new \RuntimeException('could not decode contents of app manifest schema file from: ' .$filePath);
    }
}


