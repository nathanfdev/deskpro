<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class ChatHandlerTest.
 */
class ChatHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('chat_messages');
        $this->clearTable('chat_conversations');
        $this->clearTable('people');

        $brand = new Brand();
        $brand->setName('Brand');
        $this->em()->persist($brand);

        $this->em()->flush();

        $brandStack = $this->get('brand_stack');
        $brandStack->push($brand);

        $dep = $this->createDepartment('department');
        $this->em()->persist($dep);

        $this->em()->flush();

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\Chat());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_and_update_entity()
    {
        $model = $this->createBaseModel();
        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertEquals('subject', $entity->getSubjectLine());

        $model->setSubject('subject_updated');
        $this->writer->writeModel($model);
        $this->em()->clear();

        $updatedEntity = $this->getBaseEntity();
        $this->assertNotNull($updatedEntity);
        $this->assertEquals('subject_updated', $updatedEntity->getSubjectLine());
        $this->assertEquals($entity->getId(), $updatedEntity->getId());
    }

    public function test_create_and_update_chat_message()
    {
        // create model
        $model = $this->createBaseModel();
        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->getMessages());
        $this->assertEquals('message content', $entity->getMessages()[0]->getContentHtml());

        // update model
        $model->getMessages()[0]->setContent('message content updated');

        $message2 = new Model\ChatMessage();
        $message2->setOid(2);
        $message2->setContent('message content 2');
        $model->addMessage($message2);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $updatedEntity = $this->getBaseEntity();
        $this->assertNotNull($updatedEntity);
        $this->assertCount(2, $entity->getMessages());
        $this->assertEquals('message content updated', $entity->getMessages()[0]->getContentHtml());
        $this->assertEquals('message content 2', $entity->getMessages()[1]->getContentHtml());
    }

    public function test_full_params()
    {
        $model = $this->createBaseModel();
        $model->setEndedBy('user');
        $model->setPerson(1);
        $model->setAgent(2);
        $model->setRatingComment('feedback comment');
        $model->setRatingOverall(5);
        $model->setDateCreated(new \DateTime('2016-07-10'));
        $model->setDateEnded(new \DateTime('2016-07-11'));
        $model->setLabels(['label 1', 'label 2']);

        $model->getMessages()[0]->setPerson(3);
        $model->getMessages()[0]->setDateCreated(new \DateTime('2016-07-11'));

        $customField1 = new Model\CustomField();
        $customField1->setOid(1);
        $customField1->setValue('custom val');
        $model->addCustomField($customField1);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertEquals('subject', $entity->getSubjectLine());
        $this->assertEquals('user', $entity->getEndedBy());
        $this->assertEquals('imported.user.1@example.com', $entity->getPerson()->getPrimaryEmailAddress());
        $this->assertEquals('imported.agent.2@example.com', $entity->getAgent()->getPrimaryEmailAddress());
        $this->assertEquals(5, $entity->getRatingOverall());
        $this->assertEquals('feedback comment', $entity->getRatingComment());
        $this->assertEquals('2016-07-10', $entity->getDateCreated()->format('Y-m-d'));
        $this->assertEquals('2016-07-11', $entity->getDateEnded()->format('Y-m-d'));

        $this->assertCount(2, $entity->getLabels());
        $this->assertEquals('label 1', $entity->getLabels()[0]->getLabel());
        $this->assertEquals('label 2', $entity->getLabels()[1]->getLabel());

        $this->assertCount(1, $entity->getMessages());
        $this->assertEquals('message content', $entity->getMessages()[0]->getContentHtml());
        $this->assertEquals('imported.user.3@example.com', $entity->getMessages()[0]->getAuthor()->getPrimaryEmailAddress());
        $this->assertEquals('2016-07-11', $entity->getMessages()[0]->getDateCreated()->format('Y-m-d'));

        $this->assertCount(1, $entity->getCustomData());
        $this->assertEquals('custom val', $entity->getCustomData()[0]->getInput());
    }

    /**
     * @return Model\Chat
     */
    private function createBaseModel()
    {
        $message1 = new Model\ChatMessage();
        $message1->setOid(1);
        $message1->setContent('message content');

        $model = new Model\Chat();
        $model->setOid(1);
        $model->setSubject('subject');
        $model->setEndedBy('timeout');
        $model->addMessage($message1);

        return $model;
    }

    /**
     * @return Brand
     */
    private function getBrand()
    {
        return $this->getRepository(Brand::class)->findOneBy([]);
    }

    /**
     * @param string          $title
     * @param Department|null $parent
     *
     * @return Department
     */
    private function createDepartment($title, Department $parent = null)
    {
        $department                     = new Department();
        $department->is_tickets_enabled = 1;
        $department->is_chat_enabled    = 1;
        $department->addBrand($this->getBrand());
        $department->setRealTitle($title);

        if ($parent) {
            $department->setParent($parent);
        }

        return $department;
    }

    /**
     * @return Entity\ChatConversation
     */
    private function getBaseEntity()
    {
        return $this->em()->getRepository(Entity\ChatConversation::class)->findOneBy([]);
    }
}
