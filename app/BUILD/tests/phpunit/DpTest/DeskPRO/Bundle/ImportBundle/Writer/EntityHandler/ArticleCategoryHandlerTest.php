<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class ArticleCategoryHandlerTest.
 */
class ArticleCategoryHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('article_categories');
        $this->clearTable('usergroups');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\ArticleCategory());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_entity()
    {
        $model = new Model\ArticleCategory();
        $model->setOid(1);
        $model->setTitle('test_cat');

        $this->writer->writeModel($model);
        $entity = $this->getArticleCategoryEntity();

        $importMap = $this->em()->getRepository(Entity\ImportMap::class)->findOneBy([
            'typename' => $this->get('dp.importer.writer.mapper.import_map')->getImportMapKey($model),
            'old_id'   => 1,
        ]);

        $this->assertNotNull($entity);
        $this->assertNotNull($importMap);
        $this->assertEquals($entity->getId(), $importMap->getNewId());
    }

    public function test_update_entity_by_oid()
    {
        $model = new Model\ArticleCategory();
        $model->setOid(1);
        $model->setTitle('test_cat');

        $this->writer->writeModel($model);

        $entity = $this->em()->getRepository(Entity\ArticleCategory::class)->findOneBy([
            'title' => 'test_cat',
        ]);

        $this->em()->clear();

        $model->setTitle('test_cat_updated');
        $this->writer->writeModel($model);

        $updatedEntity = $this->getArticleCategoryEntity('test_cat_updated');

        $this->assertNotNull($updatedEntity);
        $this->assertEquals($entity->getId(), $updatedEntity->getId());
    }

    public function test_update_entity_by_title()
    {
        $model = new Model\ArticleCategory();
        $model->setTitle('test_cat');

        $this->writer->writeModel($model);
        $entity = $this->getArticleCategoryEntity();

        $this->em()->clear();

        $subModel1 = new Model\ArticleSubCategory();
        $subModel1->setTitle('sub_cat_1');
        $model->addCategory($subModel1);

        $this->writer->writeModel($model);
        $updateEntity = $this->getArticleCategoryEntity();

        $this->assertEquals($entity->getId(), $updateEntity->getId());
        $this->assertCount(1, $updateEntity->getChildren());
    }

    public function test_check_props()
    {
        $model = new Model\ArticleCategory();
        $model->setOid(1);
        $model->setTitle('test_cat');
        $model->setAsAgent(true);
        $model->setAsBook(true);
        $model->addUserGroup('Group 1');
        $model->addUserGroup('Group 2');

        $this->writer->writeModel($model);
        $entity = $this->getArticleCategoryEntity();

        $this->assertNotNull($entity);
        $this->assertEquals('test_cat', $entity->getTitle());
        $this->assertEquals('test_cat', $entity->getRealTitle());
        $this->assertTrue($entity->isAgent());
        $this->assertTrue($entity->isBook());

        $this->assertCount(2, $entity->getUserGroups());
        $this->assertEquals('Group 1', $entity->getUserGroups()[0]->getTitle());
        $this->assertEmpty($entity->getUserGroups()[0]->getSysName());
        $this->assertEquals('Group 2', $entity->getUserGroups()[1]->getTitle());
        $this->assertEmpty($entity->getUserGroups()[1]->getSysName());
    }

    public function test_update_user_groups()
    {
        $model = new Model\ArticleCategory();
        $model->setOid(1);
        $model->setTitle('test_cat');
        $model->addUserGroup('Group 1');
        $model->addUserGroup('Group 2');

        $this->writer->writeModel($model);

        $entity = $this->em()->getRepository(Entity\ArticleCategory::class)->findOneBy([
            'title' => 'test_cat',
        ]);

        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getUserGroups());

        $ug1 = $entity->getUserGroups()[0];
        $ug2 = $entity->getUserGroups()[1];

        $this->em()->clear();

        $model = new Model\ArticleCategory();
        $model->setOid(1);
        $model->setTitle('test_cat');
        $model->addUserGroup('Group 1');
        $model->addUserGroup('Group 3');

        $this->writer->writeModel($model);
        $entity = $this->getArticleCategoryEntity();

        $this->assertNotNull($entity);
        $this->assertCount(3, $entity->getUserGroups());

        $this->assertEquals('Group 1', $entity->getUserGroups()[0]->getTitle());
        $this->assertEmpty($entity->getUserGroups()[0]->getSysName());
        $this->assertEquals('Group 2', $entity->getUserGroups()[1]->getTitle());
        $this->assertEmpty($entity->getUserGroups()[1]->getSysName());
        $this->assertEquals('Group 3', $entity->getUserGroups()[2]->getTitle());
        $this->assertEmpty($entity->getUserGroups()[2]->getSysName());

        $this->assertEquals($ug1->getId(), $entity->getUserGroups()[0]->getId());
        $this->assertEquals($ug2->getId(), $entity->getUserGroups()[1]->getId());
    }

    public function test_sub_categories()
    {
        $subModel1 = new Model\ArticleSubCategory();
        $subModel1->setTitle('sub_cat_1');

        $subModel1a = new Model\ArticleSubCategory();
        $subModel1a->setTitle('sub_cat_1a');

        $subModel1b = new Model\ArticleSubCategory();
        $subModel1b->setTitle('sub_cat_1b');

        $subModel1->addCategory($subModel1a);
        $subModel1->addCategory($subModel1b);

        $subModel2 = new Model\ArticleSubCategory();
        $subModel2->setTitle('sub_cat_2');

        $model = new Model\ArticleCategory();
        $model->setOid(1);
        $model->setTitle('test_cat');

        $model->addCategory($subModel1);
        $model->addCategory($subModel2);

        $this->writer->writeModel($model);
        $entity = $this->getArticleCategoryEntity();

        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getChildren());
        $this->assertEquals('sub_cat_1', $entity->getChildren()[0]->getTitle());
        $this->assertEquals('sub_cat_2', $entity->getChildren()[1]->getTitle());

        $this->assertCount(2, $entity->getChildren()[0]->getChildren());
        $this->assertEquals('sub_cat_1a', $entity->getChildren()[0]->getChildren()[0]->getTitle());
        $this->assertEquals('sub_cat_1b', $entity->getChildren()[0]->getChildren()[1]->getTitle());
    }

    public function test_update_sub_choice_by_oid()
    {
        $subModel1 = new Model\ArticleSubCategory();
        $subModel1->setOid(2);
        $subModel1->setTitle('sub_cat_1');

        $subModel1a = new Model\ArticleSubCategory();
        $subModel1a->setTitle('sub_cat_1a');

        $subModel1b = new Model\ArticleSubCategory();
        $subModel1b->setTitle('sub_cat_1b');

        $subModel1->addCategory($subModel1a);
        $subModel1->addCategory($subModel1b);

        $subModel2 = new Model\ArticleSubCategory();
        $subModel2->setTitle('sub_cat_2');

        $model = new Model\ArticleCategory();
        $model->setOid(1);
        $model->setTitle('test_cat');

        $model->addCategory($subModel1);
        $model->addCategory($subModel2);

        $this->writer->writeModel($model);
        $entity = $this->getArticleCategoryEntity();

        $subCat1 = $entity->getChildren()[0];
        $this->assertEquals('sub_cat_1', $subCat1->getTitle());

        $this->em()->clear();
        $subModel1->setTitle('sub_cat_1_updated');

        $this->writer->writeModel($model);
        $updatedEntity = $this->getArticleCategoryEntity();

        $updatedCat = $updatedEntity->getChildren()[0];

        $this->assertEquals('sub_cat_1_updated', $updatedCat->getTitle());
        $this->assertEquals($subCat1->getId(), $updatedCat->getId());
        $this->assertCount(2, $updatedCat->getChildren());
    }

    /**
     * @param string $name
     *
     * @return Entity\ArticleCategory
     */
    private function getArticleCategoryEntity($name = 'test_cat')
    {
        return $this->em()->getRepository(Entity\ArticleCategory::class)->findOneBy([
            'title' => $name,
        ]);
    }
}
