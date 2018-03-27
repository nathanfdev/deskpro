<?php

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


