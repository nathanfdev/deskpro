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

use Application\DeskPRO\Entity\BlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Designer\AdvancedEditsManager;
use Doctrine\ORM\EntityManager;
use DpTest\PortalTestCase;

/**
 * Class AdvancedEditsManagerIntegrationTest.
 */
class AdvancedEditsManagerIntegrationTest extends PortalTestCase
{
    /**
     * @var AdvancedEditsManager
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
     * @var array Dummy advanced edits data
     */
    private $dummy_data = [
        'main_scss'   => 'body { background: #fff; color: #000; }',
        'custom_scss' => '.dp-dummy-style { border-left: 42px dotted purple; }',
        'javascript'  => 'console.log("Hello, custom JS!");',
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->em = $this->getEntityManager();
        /** @var \Twig_Environment $twig */
        $twig                 = $this->get('twig');
        $this->edit_theme_set = new ThemeSet();
        $this->edit_theme_set->setThemeId('edit_theme_set_id');
        $this->em->persist($this->edit_theme_set);
        $this->em->flush();

        $this->service = new AdvancedEditsManager(
            $this->em,
            $this->getContainer()->get('blob.storage'),
            new ThemeSet(),
            $this->edit_theme_set,
            $twig,
            __DIR__.'/scss/main.scss'
        );
    }

    /**
     * Save the $dummy_data with AdvancedEditsManager.
     */
    private function saveDummyData()
    {
        $this->service->save($this->dummy_data);
    }

    // Helper methods --------------------------------------------------------------------------------------------------

    /**
     * @return null|ThemeSetAsset
     */
    private function findCustomScss()
    {
        return $this->em->getRepository(ThemeSetAsset::class)->findOneBy([
            'name'      => AdvancedEditsManager::CUSTOM_SCSS_ASSET_NAME,
            'theme_set' => $this->edit_theme_set,
        ]);
    }

    /**
     * @return null|ThemeSetAsset
     */
    private function findMainScss()
    {
        return $this->em->getRepository(ThemeSetAsset::class)->findOneBy([
            'name'      => AdvancedEditsManager::MAIN_SCSS_ASSET_NAME,
            'theme_set' => $this->edit_theme_set,
        ]);
    }

    /**
     * @return null|ThemeSetAsset
     */
    private function findCustomJs()
    {
        return $this->em->getRepository(ThemeSetAsset::class)->findOneBy([
            'name'      => AdvancedEditsManager::CUSTOM_JS_ASSET_NAME,
            'theme_set' => $this->edit_theme_set,
        ]);
    }

    /**
     * @param ThemeSetAsset $asset
     *
     * @return null|BlobStorage
     */
    private function findAssetStorage(ThemeSetAsset $asset)
    {
        if (!$blob = $asset->getBlob()) {
            return;
        }

        return $this->em->getRepository(BlobStorage::class)->findOneBy(['blob_id' => $blob->getId()]);
    }

    /**
     * Removes all custom edits.
     */
    private function removeCustomEdits()
    {
        if ($mainScss = $this->findMainScss()) {
            $this->em->remove($mainScss);
        }
        if ($customScss = $this->findCustomScss()) {
            $this->em->remove($customScss);
        }
        if ($js = $this->findCustomJs()) {
            $this->em->remove($js);
        }
        $this->em->flush();
    }

    // Tests -----------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function it_should_get_back_saved_edits()
    {
        $this->saveDummyData();
        $this->assertEquals($this->service->get(), $this->dummy_data);
    }

    /**
     * @test
     */
    public function it_should_get_main_scss_from_filesystem()
    {
        $this->removeCustomEdits();
        $this->assertEquals($this->service->getMainScss(), 'body { background: #fff; }');
    }

    /**
     * @test
     */
    public function it_should_create_custom_SCSS_ThemeSetAsset()
    {
        $this->removeCustomEdits();
        $this->saveDummyData();
        $this->assertNotNull($this->findCustomScss());
    }

    /**
     * @test
     */
    public function it_should_update_contents_of_the_existing_custom_SCSS_ThemeSetAsset()
    {
        $this->saveDummyData();
        $this->dummy_data['custom_scss'] = 'Modified SCSS';
        $this->saveDummyData();

        $this->assertNotNull($scss = $this->findCustomScss());
        $this->assertNotNull($scss->getBlob());
        $this->assertNotNull($storage = $this->findAssetStorage($scss));
        $this->assertEquals($storage->getData(), 'Modified SCSS');
    }

    /**
     * @test
     */
    public function it_should_assign_custom_SCSS_to_the_edit_ThemeSet()
    {
        $this->saveDummyData();
        $theme_set = $this->findCustomScss() ? $this->findCustomScss()->getThemeSet() : null;
        $this->assertEquals($theme_set, $this->edit_theme_set);
    }

    /**
     * @test
     */
    public function it_should_assign_the_proper_name_and_tag_to_the_SCSS_ThemeSetAsset()
    {
        $this->saveDummyData();
        $asset = $this->findCustomScss();
        $this->assertNotNull($asset);
        $this->assertEquals($asset->getName(), AdvancedEditsManager::CUSTOM_SCSS_ASSET_NAME);
        $this->assertContains(AdvancedEditsManager::CUSTOM_SCSS_ASSET_TAG, $asset->getTags());
    }

    /**
     * @test
     */
    public function it_should_create_custom_JS_ThemeSetAsset()
    {
        $this->removeCustomEdits();
        $this->saveDummyData();
        $this->assertNotNull($this->findCustomJs());
    }

    /**
     * @test
     */
    public function it_should_update_contents_of_the_existing_custom_JS_ThemeSetAsset()
    {
        $this->saveDummyData();
        $this->dummy_data['javascript'] = 'Modified JS';
        $this->saveDummyData();

        $this->assertNotNull($js = $this->findCustomJs());
        $this->assertNotNull($js->getBlob());
        $this->assertNotNull($storage = $this->findAssetStorage($js));
        $this->assertEquals($storage->getData(), 'Modified JS');
    }

    /**
     * @test
     */
    public function it_should_assign_custom_JS_to_the_edit_ThemeSet()
    {
        $this->saveDummyData();
        $theme_set = $this->findCustomJs() ? $this->findCustomJs()->getThemeSet() : null;
        $this->assertEquals($theme_set, $this->edit_theme_set);
    }

    /**
     * @test
     */
    public function it_should_assign_the_proper_name_and_tag_to_the_JS_ThemeSetAsset()
    {
        $this->saveDummyData();
        $asset = $this->findCustomJs();
        $this->assertNotNull($asset);
        $this->assertEquals($asset->getName(), AdvancedEditsManager::CUSTOM_JS_ASSET_NAME);
        $this->assertContains(AdvancedEditsManager::CUSTOM_JS_ASSET_TAG, $asset->getTags());
    }
}
