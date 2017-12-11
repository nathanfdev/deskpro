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

namespace DpTest\Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use Application\ImportBundle\Model;

/**
 * Class ArticleTest.
 */
class ArticleHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('article_categories');
        $this->clearTable('articles');
        $this->clearTable('people');
        $this->clearTable('custom_def_article');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeData(new Model\Article());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_and_update_entity()
    {
        $model = $this->createBaseModel();
        $this->writer->writeData($model);
        $entity    = $this->getBaseEntity();
        $importMap = $this->em()->getRepository(Entity\ImportMap::class)->findOneBy([
            'typename' => $this->get('dp.importer.writer.mapper.import_map')->getImportMapKey($model),
            'old_id'   => 1,
        ]);

        $this->assertNotNull($entity);
        $this->assertNotNull($importMap);
        $this->assertEquals($entity->getId(), $importMap->getNewId());

        $this->em()->clear();
        $model->setTitle('article_updated');
        $this->writer->writeData($model);

        $entityUpdated = $this->getBaseEntity('article_updated');

        $this->assertNotNull($entityUpdated);
        $this->assertEquals($entity->getId(), $entityUpdated->getId());
    }

    public function test_create_and_update_labels()
    {
        $model = $this->createBaseModel();
        $model->setLabels(['label 1', 'label 2']);

        $this->writer->writeData($model);
        $entity = $this->getBaseEntity();

        $this->assertCount(2, $entity->getLabels());
        $this->assertEquals('label 1', $entity->getLabels()[0]->getLabel());
        $this->assertEquals('label 2', $entity->getLabels()[1]->getLabel());

        $this->em()->clear();
        $model->setLabels(['label 1', 'label 3', 'label 4']);
        $this->writer->writeData($model);
        $entity = $this->getBaseEntity();

        $this->assertCount(3, $entity->getLabels());
        $this->assertEquals('label 1', $entity->getLabels()[0]->getLabel());
        $this->assertEquals('label 3', $entity->getLabels()[1]->getLabel());
        $this->assertEquals('label 4', $entity->getLabels()[2]->getLabel());
    }

    public function test_no_person()
    {
        $model = $this->createBaseModel();
        $this->writer->writeData($model);
        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertNull($entity->getPerson());
    }

    public function test_unset_person()
    {
        $model = $this->createBaseModel();
        $model->setPerson(1);

        $this->writer->writeData($model);
        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertNotNull($entity->getPerson());

        $this->em()->clear();
        $model->setPerson(null);

        $this->writer->writeData($model);
        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertNull($entity->getPerson());
    }

    public function test_import_new_person_by_email()
    {
        $model = $this->createBaseModel();
        $model->setPerson('unknown_email@deskpro.dev');

        $this->writer->writeData($model);
        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertNotNull($entity->getPerson());
        $this->assertEquals('unknown_email@deskpro.dev', $entity->getPerson()->getEmailAddress());
    }

    public function test_create_article_with_comments()
    {
        $comment1 = new Model\Comment();
        $comment1->setContent('comment content 1');
        $comment1->setStatus('visible');

        $comment2 = new Model\Comment();
        $comment2->setContent('comment content 2');
        $comment2->setStatus('visible');

        $model = $this->createBaseModel();
        $model->addComment($comment1);
        $model->addComment($comment2);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getComments()->toArray());
    }

    public function test_update_article_comment_by_oid()
    {
        $comment1 = new Model\Comment();
        $comment1->setOid(1);
        $comment1->setContent('comment content 1');
        $comment1->setStatus('visible');

        $model = $this->createBaseModel();
        $model->addComment($comment1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $oldId  = $entity->getComments()->first()->getId();

        $comment1->setContent('comment content updated');

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->getComments()->toArray());
        $this->assertEquals('comment content updated', $entity->getComments()->first()->getContent());
        $this->assertEquals($oldId, $entity->getComments()->first()->getId());
    }

    public function test_create_and_update_article_with_attachments()
    {
        $attachment1 = $this->createAttachmentModel('file1.txt');
        $attachment1->setOid(1);
        $attachment2 = $this->createAttachmentModel('file2.txt');
        $attachment2->setOid(2);

        $model = $this->createBaseModel();
        $model->addAttachment($attachment1);
        $model->addAttachment($attachment2);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getAttachments()->toArray());

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getAttachments()->toArray());
    }

    public function test_create_and_update_article_with_categories()
    {
        $model = $this->createBaseModel();
        $model->setCategories(['category 1 > sub category 2', 'category 3']);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getCategories());
        $this->assertEquals('sub category 2', $entity->getCategories()[0]->getTitle());
        $this->assertEquals('category 1', $entity->getCategories()[0]->getParent()->getTitle());
        $this->assertEquals('category 3', $entity->getCategories()[1]->getTitle());

        $model->setCategories(['category 1 > sub category 2 > sub category 3', 'category 4']);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(4, $entity->getCategories());
        $this->assertEquals('sub category 2', $entity->getCategories()[0]->getTitle());
        $this->assertEquals('category 1', $entity->getCategories()[0]->getParent()->getTitle());
        $this->assertEquals('category 3', $entity->getCategories()[1]->getTitle());
        $this->assertEquals('sub category 3', $entity->getCategories()[2]->getTitle());
        $this->assertEquals('sub category 2', $entity->getCategories()[2]->getParent()->getTitle());
        $this->assertEquals('category 1', $entity->getCategories()[2]->getParent()->getParent()->getTitle());
        $this->assertEquals('category 4', $entity->getCategories()[3]->getTitle());
    }

    public function test_create_and_update_article_with_custom_data()
    {
        $customField1 = new Model\CustomField();
        $customField1->setName('field 1');
        $customField1->setValue('val1');

        $customField2 = new Model\CustomField();
        $customField2->setOid(1);
        $customField2->setValue('val2');

        $model = $this->createBaseModel();
        $model->addCustomField($customField1);
        $model->addCustomField($customField2);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getCustomData());
        $this->assertEquals('val1', $entity->getCustomData()[0]->getInput());
        $this->assertEquals('field 1', $entity->getCustomData()[0]->getRootField()->getTitle());

        $this->assertEquals('val2', $entity->getCustomData()[1]->getInput());
        $this->assertEquals('Custom field 1', $entity->getCustomData()[1]->getRootField()->getTitle());
    }

    public function test_dynamically_add_choice_defs()
    {
        $customDef = new Entity\CustomDefArticle();
        $customDef->setTitle('field 1');
        $customDef->setWidgetType('choice');

        $firstChoiceDef = new Entity\CustomDefArticle();
        $firstChoiceDef->setTitle('choice 1');

        $customDef->addChild($firstChoiceDef);

        $this->em()->persist($customDef);
        $this->em()->flush();

        $customField1 = new Model\CustomField();
        $customField1->setName('field 1');
        $customField1->setValue('choice 1 > sub choice 1 > sub choice 1a');

        $model = $this->createBaseModel();
        $model->addCustomField($customField1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->getCustomData());
        $this->assertEquals('field 1', $entity->getCustomData()[0]->getRootField()->getTitle());
        $this->assertEquals('sub choice 1a', $entity->getCustomData()[0]->getField()->getTitle());
        $this->assertNotNull($entity->getCustomData()[0]->getField()->getOption('parent_id'));

        $choiceDef = $this->em()->getRepository(Entity\CustomDefArticle::class)->find($entity->getCustomData()[0]->getField()->getOption('parent_id'));
        $this->assertNotNull($choiceDef);
        $this->assertEquals('sub choice 1', $choiceDef->getTitle());
    }

    public function test_remove_choice_data()
    {
        $customDef = new Entity\CustomDefArticle();
        $customDef->setTitle('field 1');
        $customDef->setWidgetType('choice');

        $this->em()->persist($customDef);
        $this->em()->flush();

        $customField1 = new Model\CustomField();
        $customField1->setName('field 1');
        $customField1->setValue('choice 1, choice 2, choice 3');

        $model = $this->createBaseModel();
        $model->addCustomField($customField1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(3, $entity->getCustomData());

        $customField1->setValue('choice 1, choice 4');

        $model = $this->createBaseModel();
        $model->addCustomField($customField1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getCustomData());
    }

    public function test_default_category()
    {
        $category = new Entity\ArticleCategory();
        $category->setRealTitle('cat');

        $this->em()->persist($category);
        $this->em()->flush($category);

        $model = $this->createBaseModel();

        $this->writer->writeData($model);

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertNotEmpty($entity->getCategories());
    }

    /**
     * @return Model\Article
     */
    private function createBaseModel()
    {
        $model = new Model\Article();
        $model->setOid(1);
        $model->setTitle('article');
        $model->setContent('content');
        $model->setStatus('published');

        return $model;
    }

    /**
     * @param string $name
     *
     * @return Entity\Article
     */
    private function getBaseEntity($name = 'article')
    {
        return $this->em()->getRepository(Entity\Article::class)->findOneBy([
            'title' => $name,
        ]);
    }
}
