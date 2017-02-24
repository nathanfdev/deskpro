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

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use JsonSchema;
use Doctrine\ORM;

class ApplicationService implements Domain\ApplicationCreator
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var JsonSchema\Validator  */
    private $schemaValidator;

    /** @var  \SplFileInfo */
    private $schema;

    /**
     * ApplicationService constructor.
     * @param ORM\EntityManager $entityManager
     * @param JsonSchema\Validator $schemaValidator
     * @param \SplFileInfo $schema
     */
    public function __construct(
        ORM\EntityManager $entityManager
        , JsonSchema\Validator $schemaValidator
        , \SplFileInfo $schema
    ) {
        $this->entityManager = $entityManager;
        $this->schemaValidator = $schemaValidator;
        $this->schema = $schema;
    }

    public function verifyBundle(Domain\AppBundle $bundle)
    {
        $manifestString = $bundle->getManifestAsString();
        $manifestData = json_decode($manifestString);
        if (empty($manifestData) || false == $manifestData instanceof \stdClass) {
            return false;
        }

        $this->schemaValidator->validate($manifestData, (object)['$ref' => 'file://' . $this->schema->getRealPath()]);
        return $this->schemaValidator->isValid();
    }

    public function createApplication(Domain\AppBundle $bundle)
    {
        $appEntity = $this->mapManifestStringToApp($bundle->getManifestAsString(), new Entity\AppStore\App());

        $assets = [];
        foreach ($bundle->listAllResources() as $resource) {
            $asset = new Entity\AppStore\AppAsset();
            $asset->setApp($appEntity);

            $assets[] = $this->mapBundleResourceToAsset($resource, $asset);
        }

        $this->persistApplication($appEntity, $assets);
        return $appEntity;
    }

    /**
     * @param Entity\AppStore\App $app
     * @param array|Entity\AppStore\AppAsset[] $assets
     */
    private function persistApplication(Entity\AppStore\App $app, array $assets)
    {
        $this->entityManager->persist($app);
        foreach ($assets as $asset) {
            $this->entityManager->persist($asset);
        }
        $this->entityManager->flush();
    }

    /**
     * @param string $manifestString
     * @param Entity\AppStore\App $app
     * @return Entity\AppStore\App
     */
    private function mapManifestStringToApp($manifestString, Entity\AppStore\App $app)
    {
       $manifestReader = new Infrastructure\AppManifestJsonReader();
       $manifest = $manifestReader->readManifest($manifestString);

       $app->setManifest($manifestString);

       $value = $manifest->getName();
       $app->setName($value);

       return $app;
    }

    private function mapBundleResourceToAsset(Domain\AppBundleResource $resource, Entity\AppStore\AppAsset $asset)
    {
        $asset->setPath(
            $resource->getPath()
        );

        $asset->setContent(
            $resource->getContent()
        );

        return $asset;
    }
}


