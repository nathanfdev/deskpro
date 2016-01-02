<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DpTest\Bundle\AppBundle\DataService\Content;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\BlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Designer\ThemeSetCopyingService;
use DpTest\PortalTestCase;

/**
 * Class ThemeSetCopyingServiceIntegrationTest.
 */
class ThemeSetCopyingServiceIntegrationTest extends PortalTestCase
{
    /**
     * @var ThemeSetCopyingService
     */
    private $service;

    /**
     * @var ThemeSet
     */
    private $source;

    /**
     * @var ThemeSet
     */
    private $destination;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        // Retrieve the service from container
        $this->service = $this->getContainer()->get('dp.portal.designer.theme_set_copying_service');

        // Fake source ThemeSet
        $this->source = new ThemeSet();
        $this->source->setThemeId($id = 'theme_id_'.uniqid());
        $this->source->setOptions(['fake' => 'option', 'another' => 1]);

        // Fake destination ThemeSet
        $this->destination = new ThemeSet();
        $this->destination->setThemeId("another_$id");

        $em = $this->getEntityManager();
        $em->persist($this->source);
        $em->persist($this->destination);
        $em->flush();
    }

    /**
     * @test
     */
    public function it_should_be_a_service()
    {
        $this->assertInstanceOf(ThemeSetCopyingService::class, $this->service);
    }

    /**
     * @test
     */
    public function it_should_copy_theme_id_and_options()
    {
        $this->copy();
        $this->assertEquals($this->source->getThemeId(), $this->destination->getThemeId());
        $this->assertEquals($this->source->getOptions(), $this->destination->getOptions());
    }

    /**
     * @test
     */
    public function it_should_copy_related_ThemeSetAsset_entities()
    {
        // given 3 assets on the source
        $this->persistDummyThemeSetAsset($this->source);
        $this->persistDummyThemeSetAsset($this->source);
        $this->persistDummyThemeSetAsset($this->source);

        // when
        $this->copy();

        // then
        $this->assertCount(3, $this->findAssets($this->destination));
    }

    /**
     * @test
     */
    public function it_should_clone_ThemeSetAsset_as_new_entity()
    {
        $asset = $this->persistDummyThemeSetAsset($this->source);
        $this->copy();
        $this->assertNotEquals($asset->getId(), $this->findAssets($this->destination)[0]->getId());
    }

    /**
     * @test
     */
    public function it_should_drop_existing_ThemeSetAsset_entities_on_the_destination_ThemeSet()
    {
        // given 2 assets on the source and 1 on the destination
        $this->persistDummyThemeSetAsset($this->source, true, 'First');
        $this->persistDummyThemeSetAsset($this->source, true, 'Second');
        $this->persistDummyThemeSetAsset($this->destination, true, 'First destination');

        // when
        $this->copy();

        // then
        $assets = $this->findAssets($this->destination);
        $this->assertCount(2, $assets);
        $names = array_map(function (ThemeSetAsset $asset) { return $asset->getName(); }, $assets);
        $this->assertArraySubset(['First', 'Second'], $names);
    }

    /**
     * @test
     */
    public function it_should_copy_assets_together_with_blobs()
    {
        $asset = $this->persistDummyThemeSetAsset($this->source);
        $blob  = $asset->getBlob();

        $this->copy();

        $blob2 = $this->findAssets($this->destination)[0]->getBlob();
        $this->assertEquals($blob->blob_hash, $blob2->blob_hash);
        $this->assertEquals($blob->filename, $blob2->filename);
    }

    /**
     * @test
     */
    public function it_should_clone_asset_blobs_as_new_entities()
    {
        $asset = $this->persistDummyThemeSetAsset($this->source);
        $blob  = $asset->getBlob();

        $this->copy();

        $blob2 = $this->findAssets($this->destination)[0]->getBlob();
        $this->assertNotEquals($blob->getId(), $blob2->getId());
    }

    /**
     * @test
     */
    public function it_should_copy_blobs_together_with_blob_storage()
    {
        $asset   = $this->persistDummyThemeSetAsset($this->source);
        $blob    = $asset->getBlob();
        $storage = $this->findBlobStorage($blob);

        $this->copy();

        $blob2    = $this->findAssets($this->destination)[0]->getBlob();
        $storage2 = $this->findBlobStorage($blob2);
        $this->assertEquals($storage->getData(), $storage2->getData());
    }

    /**
     * @test
     */
    public function it_should_clone_blob_storage_as_new_entity()
    {
        $asset   = $this->persistDummyThemeSetAsset($this->source);
        $blob    = $asset->getBlob();
        $storage = $this->findBlobStorage($blob);

        $this->copy();

        $blob2    = $this->findAssets($this->destination)[0]->getBlob();
        $storage2 = $this->findBlobStorage($blob2);
        $this->assertNotEquals($storage->getId(), $storage2->getId());
    }

    // Helpers ---------------------------------------------------------------------------------------------------------

    /**
     * Copy from $source to $destination using $service.
     */
    private function copy()
    {
        $this->service->copy($this->source, $this->destination);
    }

    /**
     * @param ThemeSet $theme_set
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset[]
     */
    private function findAssets(ThemeSet $theme_set)
    {
        return $this->getRepository(ThemeSetAsset::class)->findBy(['theme_set' => $theme_set]);
    }

    /**
     * @param Blob $blob
     *
     * @return BlobStorage
     */
    private function findBlobStorage(Blob $blob)
    {
        return $this->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
    }

    // Mocks -----------------------------------------------------------------------------------------------------------

    /**
     * @param ThemeSet    $theme_set
     * @param bool        $blob
     * @param null|string $name
     *
     * @return ThemeSetAsset
     */
    private function persistDummyThemeSetAsset(ThemeSet $theme_set, $blob = true, $name = null)
    {
        $asset = new ThemeSetAsset();
        $asset->setThemeSet($theme_set);
        $asset->setName($name || uniqid());
        $asset->setTags(['test']);
        if ($blob) {
            $asset->setBlob($this->persistDummyBlob());
        }

        $this->getEntityManager()->persist($asset);
        $this->getEntityManager()->flush();

        return $asset;
    }

    /**
     * @param bool $file
     *
     * @return Blob
     */
    private function persistDummyBlob($file = true)
    {
        $em = $this->getEntityManager();

        $blob = new Blob();
        $blob->setFilename(uniqid());
        $blob->blob_hash    = md5('test');
        $blob->content_type = 'text/css';
        $em->persist($blob);
        $em->flush();

        if ($file) {
            $storage          = new BlobStorage();
            $storage->blob_id = $blob->getId();
            $storage->data    = uniqid();
            $em->persist($storage);
            $em->flush();
        }

        return $blob;
    }
}
