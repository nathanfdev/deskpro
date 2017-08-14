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
use Application\ImportBundle\Writer\Mapper\ImportMapMapper;

/**
 * Class TicketHandlerTest.
 */
class TicketHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('departments');
        $this->clearTable('tickets');
        $this->clearTable('ticket_categories');
        $this->clearTable('ticket_workflows');
        $this->clearTable('ticket_priorities');
        $this->clearTable('products');
        $this->clearTable('people');
        $this->clearTable('organizations');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeData(new Model\Ticket());
        $this->assertTrue($this->loggerHandler->hasWarningRecords());
    }

    public function test_create_and_update_entity()
    {
        $model = $this->createBaseModel();
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity    = $this->getBaseEntity();
        $importMap = $this->em()->getRepository(Entity\ImportMap::class)->findOneBy([
            'typename' => ImportMapMapper::getImportMapKey($model),
            'old_id'   => 1,
        ]);

        $this->assertNotNull($entity);
        $this->assertNotNull($importMap);
        $this->assertEquals($entity->getId(), $importMap->getNewId());

        $this->em()->clear();
        $model->setSubject('name_updated');
        $this->writer->writeData($model);
        $this->em()->clear();

        $entityUpdated = $this->getBaseEntity('name_updated');

        $this->assertNotNull($entityUpdated);
        $this->assertEquals('name_updated', $entityUpdated->getSubject());
        $this->assertEquals($entity->getId(), $entityUpdated->getId());
    }

    public function test_check_props()
    {
        $customField1 = new Model\CustomField();
        $customField1->setOid(1);
        $customField1->setValue('custom val');

        $model = $this->createBaseModel();
        $model->setPerson('new_user@deskpro.dev');
        $model->setDepartment('New department');
        $model->setCategory('New category');
        $model->setProduct('New product > Sub product');
        $model->setWorkflow('New workflow');
        $model->setPriority('New priority');
        $model->setStatus('awaiting_user');
        $model->setLanguage('eng');
        $model->setDateCreated(new \DateTime('2016-07-01'));
        $model->setDateArchived(new \DateTime('2016-07-05'));
        $model->setDateResolved(new \DateTime('2016-07-10'));
        $model->setOrganization(2);
        $model->setLabels(['label 1', 'label 2']);
        $model->addCustomField($customField1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertEquals('subject', $entity->getSubject());
        $this->assertNotNull($entity->getPerson());
        $this->assertEquals('new_user@deskpro.dev', $entity->getPerson()->getPrimaryEmailAddress());
        $this->assertEquals('New department', $entity->getDepartment()->getTitle());
        $this->assertEquals('New category', $entity->getCategory()->getTitle());
        $this->assertEquals('Sub product', $entity->getProduct()->getTitle());
        $this->assertEquals('New product', $entity->getProduct()->getParent()->getTitle());
        $this->assertEquals('New workflow', $entity->getWorkflow()->getTitle());
        $this->assertEquals('New priority', $entity->getPriority()->getTitle());
        $this->assertEquals('awaiting_user', $entity->getStatus());
        $this->assertEquals('eng', $entity->getLanguage()->getLangCode());
        $this->assertEquals('2016-07-01', $entity->getDateCreated()->format('Y-m-d'));
        $this->assertEquals('2016-07-05', $entity->getDateArchived()->format('Y-m-d'));
        $this->assertEquals('2016-07-10', $entity->getDateResolved()->format('Y-m-d'));
        $this->assertEquals('Organization2', $entity->getOrganization()->getName());
        $this->assertCount(2, $entity->getLabels());
        $this->assertCount(1, $entity->getCustomData());
    }

    public function test_update_participants()
    {
        $model = $this->createBaseModel();
        $model->setParticipants(['participant_1@deskpro.dev', 'participant_2@deskpro.dev']);
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getParticipants());
        $this->assertEquals('participant_1@deskpro.dev', $entity->getParticipants()[0]->getPerson()->getPrimaryEmailAddress());
        $this->assertEquals('participant_2@deskpro.dev', $entity->getParticipants()[1]->getPerson()->getPrimaryEmailAddress());

        $model->setParticipants(['participant_3@deskpro.dev', 'participant_4@deskpro.dev']);
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getParticipants());
        $this->assertEquals('participant_3@deskpro.dev', $entity->getParticipants()[0]->getPerson()->getPrimaryEmailAddress());
        $this->assertEquals('participant_4@deskpro.dev', $entity->getParticipants()[1]->getPerson()->getPrimaryEmailAddress());
    }

    public function test_set_participants_by_oid()
    {
        $model = $this->createBaseModel();
        $model->setParticipants([5, 6]);
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(2, $entity->getParticipants());
        $this->assertEquals('imported.user.5@example.com', $entity->getParticipants()[0]->getPerson()->getPrimaryEmailAddress());
        $this->assertEquals('imported.user.6@example.com', $entity->getParticipants()[1]->getPerson()->getPrimaryEmailAddress());

        $model->setParticipants([6, 7, 8]);
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(3, $entity->getParticipants());
        $this->assertEquals('imported.user.6@example.com', $entity->getParticipants()[0]->getPerson()->getPrimaryEmailAddress());
        $this->assertEquals('imported.user.7@example.com', $entity->getParticipants()[1]->getPerson()->getPrimaryEmailAddress());
        $this->assertEquals('imported.user.8@example.com', $entity->getParticipants()[2]->getPerson()->getPrimaryEmailAddress());
    }

    public function test_create_and_update_ticket_messages()
    {
        $message1 = new Model\TicketMessage();
        $message1->setOid(1);
        $message1->setMessage('message');
        $message1->setPerson(1);

        $model = $this->createBaseModel();
        $model->addMessage($message1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->messages);
        $this->assertEquals('message', $entity->messages[0]->message);

        $message1->setMessage('message_updated');
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->messages);
        $this->assertEquals('message_updated', $entity->messages[0]->message);
    }

    public function test_create_and_update_ticket_message_attachments()
    {
        $attachment = new Model\Attachment();
        $attachment->setOid(1);
        $attachment->setBlobData('blob data');
        $attachment->setContentType('text/plain');
        $attachment->setFileName('file1.txt');

        $message1 = new Model\TicketMessage();
        $message1->setOid(1);
        $message1->setMessage('message');
        $message1->setPerson(1);
        $message1->addAttachment($attachment);

        $model = $this->createBaseModel();
        $model->addMessage($message1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->messages);
        $this->assertCount(1, $entity->messages[0]->getAttachments());
        $this->assertEquals('file1.txt', $entity->messages[0]->getAttachments()[0]->getBlob()->getFilename());

        $attachment->setFileName('file2.txt');
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->messages);
        $this->assertCount(1, $entity->messages[0]->getAttachments());
        $this->assertEquals('file2.txt', $entity->messages[0]->getAttachments()[0]->getBlob()->getFilename());
    }

    public function test_ticket_on_hold()
    {
        $message1 = new Model\TicketMessage();
        $message1->setOid(1);
        $message1->setMessage('message');
        $message1->setPerson(1);

        $model = $this->createBaseModel();
        $model->setAsHold(true);
        $model->addMessage($message1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertTrue($entity->isHold());
    }

    public function test_leaf_department_detect()
    {
        $brand = $this->getRepository(Entity\Brand::class)->findOneBy(['name' => 'default']);

        $department = Entity\Department::createTicketDepartment();
        $department->setRealTitle('d');
        $department->addBrand($brand);

        $department1 = Entity\Department::createTicketDepartment();
        $department1->setRealTitle('d1');
        $department1->addBrand($brand);

        $department1a = Entity\Department::createTicketDepartment();
        $department1a->setRealTitle('d1a');
        $department1a->addBrand($brand);

        $department2 = Entity\Department::createTicketDepartment();
        $department2->setRealTitle('d2');
        $department2->addBrand($brand);

        $department1->addChild($department1a);
        $department->addChild($department1);
        $department->addChild($department2);

        $this->em()->persist($department1a);
        $this->em()->persist($department1);
        $this->em()->persist($department2);
        $this->em()->persist($department);
        $this->em()->flush();

        $model = $this->createBaseModel();
        $model->setDepartment('d');

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertEquals('d1a', $entity->getDepartment()->getRealTitle());
    }

    public function test_get_brand_from_department()
    {
        $customBrand = new Entity\Brand();
        $customBrand->setName('custom brand');

        $this->em()->persist($customBrand);
        $this->em()->flush();

        $department = Entity\Department::createTicketDepartment();
        $department->setRealTitle('custom department');
        $department->addBrand($customBrand);

        $this->em()->persist($department);
        $this->em()->flush();

        $model = $this->createBaseModel();
        $model->setDepartment('custom department');

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertEquals('custom department', $entity->getDepartment()->getRealTitle());
        $this->assertEquals('custom brand', $entity->getBrand()->getName());
    }

    public function test_set_urgency()
    {
        $model = $this->createBaseModel();

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertEquals(1, $entity->getUrgency());

        $model->setUrgency(5);
        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertEquals(5, $entity->getUrgency());
    }

    public function test_existing_workflow()
    {
        $workflow = new Entity\TicketWorkflow();
        $workflow->setTitle('my workflow');

        $this->em()->persist($workflow);
        $this->em()->flush();
        $this->em()->clear();

        $model = $this->createBaseModel();
        $model->setWorkflow('my workflow');

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertEquals($workflow->getId(), $entity->getWorkflow()->getId());
    }

    public function test_existing_priority()
    {
        $priority = new Entity\TicketPriority();
        $priority->setTitle('my priority');

        $this->em()->persist($priority);
        $this->em()->flush();
        $this->em()->clear();

        $model = $this->createBaseModel();
        $model->setPriority('my priority');

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertEquals($priority->getId(), $entity->getPriority()->getId());
    }

    public function test_archived_status()
    {
        $message1 = new Model\TicketMessage();
        $message1->setOid(1);
        $message1->setMessage('message');
        $message1->setPerson(1);

        $model = $this->createBaseModel();
        $model->setStatus(Entity\Ticket::STATUS_ARCHIVED);
        $model->addMessage($message1);

        $this->writer->writeData($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertTrue($entity->isArchived());
    }

    /**
     * @return Model\Ticket
     */
    private function createBaseModel()
    {
        $model = new Model\Ticket();
        $model->setOid(1);
        $model->setSubject('subject');
        $model->setPerson(1);

        return $model;
    }

    /**
     * @param string $subject
     *
     * @return Entity\Ticket
     */
    private function getBaseEntity($subject = 'subject')
    {
        return $this->em()->getRepository(Entity\Ticket::class)->findOneBy([
            'subject' => $subject,
        ]);
    }
}
