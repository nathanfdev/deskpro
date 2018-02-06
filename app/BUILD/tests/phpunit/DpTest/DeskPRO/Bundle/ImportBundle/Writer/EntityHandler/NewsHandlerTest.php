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

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class NewsTest.
 */
class NewsHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('news');
        $this->clearTable('news_categories');
        $this->clearTable('people');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\News());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_and_update_entity()
    {
        $model = $this->createBaseModel();
        $this->writer->writeModel($model);
        $entity    = $this->getBaseEntity();
        $importMap = $this->em()->getRepository(Entity\ImportMap::class)->findOneBy([
            'typename' => $this->get('dp.importer.writer.mapper.import_map')->getImportMapKey($model),
            'old_id'   => 1,
        ]);

        $this->assertNotNull($entity);
        $this->assertNotNull($importMap);
        $this->assertEquals($entity->getId(), $importMap->getNewId());

        $this->em()->clear();
        $model->setTitle('download_updated');
        $this->writer->writeModel($model);

        $entityUpdated = $this->getBaseEntity('download_updated');

        $this->assertNotNull($entityUpdated);
        $this->assertEquals($entity->getId(), $entityUpdated->getId());
    }

    public function test_check_props()
    {
        $model = $this->createBaseModel();
        $model->setPerson('some_email@example.com');
        $model->setDateCreated(new \DateTime('2016-07-10'));
        $model->setDatePublished(new \DateTime('2016-07-20'));
        $model->setViewCount(100);
        $model->setLanguage('eng');
        $model->setCategory('Download category 1 > Sub category 1');
        $model->setLabels(['label 1', 'label 2']);

        $this->writer->writeModel($model);

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertEquals('title', $entity->getTitle());
        $this->assertEquals('content', $entity->getContentPlain());
        $this->assertEquals('published', $entity->getStatus());
        $this->assertEquals('some_email@example.com', $entity->getPerson()->getEmailAddress());
        $this->assertEquals('2016-07-10', $entity->getDateCreated()->format('Y-m-d'));
        $this->assertEquals('2016-07-20', $entity->getDatePublished()->format('Y-m-d'));
        $this->assertEquals(100, $entity->getViewCount());
        $this->assertEquals('eng', $entity->getLanguage()->getLangCode());
        $this->assertEquals('Sub category 1', $entity->getCategory()->getTitle());
        $this->assertEquals('label 1', $entity->getLabels()[0]->getLabel());
        $this->assertEquals('label 2', $entity->getLabels()[1]->getLabel());
    }

    public function test_default_category()
    {
        $category = new Entity\NewsCategory();
        $category->setRealTitle('cat');

        $this->em()->persist($category);
        $this->em()->flush($category);

        $model = $this->createBaseModel();

        $this->writer->writeModel($model);

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertNotNull($entity->getCategory());
    }

    /**
     * @return Model\News
     */
    private function createBaseModel()
    {
        $model = new Model\News();
        $model->setOid(1);
        $model->setTitle('title');
        $model->setContent('content');
        $model->setStatus('published');

        return $model;
    }

    /**
     * @param string $name
     *
     * @return Entity\News
     */
    private function getBaseEntity($name = 'title')
    {
        return $this->em()->getRepository(Entity\News::class)->findOneBy([
            'title' => $name,
        ]);
    }
}
