<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use Doctrine\ORM\EntityManager;
use DpTest\DeskPRO\Bundle\PortalBundle\Designer\Helper\PortalDesignerTestHelper;
use DpTest\PortalTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AssetsManagerIntegrationTest.
 */
class AssetsManagerIntegrationTest extends PortalTestCase
{
    use PortalDesignerTestHelper;

    /**
     * @var AssetsManager
     */
    private $service;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ThemeSet
     */
    private $edit_theme_set;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->em = $this->getEntityManager();

        $this->edit_theme_set = new ThemeSet();
        $this->edit_theme_set->setThemeId('edit_theme_set_id');
        $this->em->persist($this->edit_theme_set);
        $this->em->flush();

        $this->blobStorage = $this->getContainer()->get('blob.storage');
        $this->service     = new AssetsManager($this->em, $this->blobStorage, new ThemeSet(), $this->edit_theme_set);
    }

    /**
     * @test
     */
    public function it_should_get_list_of_assets_by_the_custom_asset_tag()
    {
        $num = 5;
        $this->cleanThemeSetAssets($this->edit_theme_set);
        $this->persistDummyThemeSetAssets($this->edit_theme_set, $num, [AssetsManager::CUSTOM_ASSET_TAG]);
        $this->persistDummyThemeSetAssets($this->edit_theme_set, 2, ['not_'.AssetsManager::CUSTOM_ASSET_TAG]);

        $assets = $this->service->getEditThemeSetAssets();

        $this->assertCount($num, $assets);
    }

    /**
     * @test
     */
    public function it_should_delete_an_asset()
    {
        $num = 3;
        $this->cleanThemeSetAssets($this->edit_theme_set);
        $this->persistDummyThemeSetAssets($this->edit_theme_set, $num, [AssetsManager::CUSTOM_ASSET_TAG]);

        $this->service->deleteEditThemeSetAsset($this->service->getEditThemeSetAssets()[0]);

        $assets = $this->service->getEditThemeSetAssets();
        $this->assertCount($num - 1, $assets);
    }

    /**
     * @test
     */
    public function it_should_save_uploaded_file_as_an_asset()
    {
        $this->cleanThemeSetAssets($this->edit_theme_set);
        $file = new UploadedFile(__DIR__.'/file/asset.txt', 'asset.txt');

        $this->service->uploadEditThemeSetAsset($file);

        $assets = $this->service->getEditThemeSetAssets();
        $this->assertCount(1, $assets);
    }

    /**
     * @test
     */
    public function it_should_save_uploaded_file_content_as_a_BlobStorage()
    {
        $this->cleanThemeSetAssets($this->edit_theme_set);
        $file = new UploadedFile($path = __DIR__.'/file/asset.txt', 'asset.txt');

        $this->service->uploadEditThemeSetAsset($file);

        $asset = $this->service->getEditThemeSetAssets()[0];
        $this->assertEquals(file_get_contents($path), $this->blobStorage->copyBlobRecordToString($asset->getBlob()));
    }

    /**
     * @test
     */
    public function it_should_replace_whitespaces_in_uploaded_file_name()
    {
        $this->cleanThemeSetAssets($this->edit_theme_set);
        $file = new UploadedFile($path = __DIR__.'/file/asset.txt', 'test asset.txt');

        $this->service->uploadEditThemeSetAsset($file);

        $asset = $this->service->getEditThemeSetAssets()[0];
        $this->stringEndsWith('test_asset.txt', $asset->getName());
    }

    /**
     * @test
     */
    public function it_should_find_blob_storage_by_asset_name()
    {
        $this->cleanThemeSetAssets($this->edit_theme_set);
        $file = new UploadedFile($path = __DIR__.'/file/asset.txt', 'asset.txt');
        $this->service->uploadEditThemeSetAsset($file);
        $blob = $this->service->getEditThemeSetAssets()[0]->getBlob();

        $this->assertEquals(file_get_contents($path), $this->blobStorage->copyBlobRecordToString($blob));
    }
}
