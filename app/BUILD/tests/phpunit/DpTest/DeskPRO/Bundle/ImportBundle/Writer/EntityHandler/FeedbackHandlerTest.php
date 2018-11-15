<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class FeedbackTest.
 */
class FeedbackHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('feedback');
        $this->clearTable('feedback_categories');
        $this->clearTable('people');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\Feedback());
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
        $model->setTitle('title_updated');
        $this->writer->writeModel($model);

        $entityUpdated = $this->getBaseEntity('title_updated');

        $this->assertNotNull($entityUpdated);
        $this->assertEquals($entity->getId(), $entityUpdated->getId());
    }

    public function test_check_props()
    {
        $customField1 = new Model\CustomField();
        $customField1->setOid(1);
        $customField1->setValue('custom val');

        $attachment = new Model\Attachment();
        $attachment->setBlobData('blob data');
        $attachment->setContentType('text/plain');
        $attachment->setFileName('file1.txt');

        $model = $this->createBaseModel();
        $model->setPerson('some_email@example.com');
        $model->setDateCreated(new \DateTime('2016-07-10'));
        $model->setDatePublished(new \DateTime('2016-07-20'));
        $model->setViewCount(100);
        $model->setLanguage('en-US');
        $model->setCategory('Category 1 > Sub category 1');
        $model->setLabels(['label 1', 'label 2']);
        $model->addCustomField($customField1);
        $model->addAttachment($attachment);

        $this->writer->writeModel($model);

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertEquals('title', $entity->getTitle());
        $this->assertEquals('content', $entity->getContentPlain());
        $this->assertEquals('active', $entity->getStatus());
        $this->assertEquals('some_email@example.com', $entity->getPerson()->getEmailAddress());
        $this->assertEquals('2016-07-10', $entity->getDateCreated()->format('Y-m-d'));
        $this->assertEquals('2016-07-20', $entity->getDatePublished()->format('Y-m-d'));
        $this->assertEquals(100, $entity->getViewCount());
        $this->assertEquals('en-US', $entity->getLanguage()->getLocale());
        $this->assertEquals('Sub category 1', $entity->getCategory()->getTitle());
        $this->assertEquals('label 1', $entity->getLabels()[0]->getLabel());
        $this->assertEquals('label 2', $entity->getLabels()[1]->getLabel());
        $this->assertCount(1, $entity->getCustomData());
        $this->assertEquals('custom val', $entity->getCustomData()[0]->getInput());
        $this->assertCount(1, $entity->getAttachments());
    }

    public function test_default_category()
    {
        $category = new Entity\FeedbackCategory();
        $category->setRealTitle('cat');

        $this->em()->persist($category);
        $this->em()->flush();

        $model = $this->createBaseModel();

        $this->writer->writeModel($model);

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertNotNull($entity->getCategory());
    }

    /**
     * @return Model\Feedback
     */
    private function createBaseModel()
    {
        $model = new Model\Feedback();
        $model->setOid(1);
        $model->setTitle('title');
        $model->setContent('content');
        $model->setStatus('active');

        return $model;
    }

    /**
     * @param string $name
     *
     * @return Entity\Feedback
     */
    private function getBaseEntity($name = 'title')
    {
        return $this->em()->getRepository(Entity\Feedback::class)->findOneBy([
            'title' => $name,
        ]);
    }
}
