<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class PersonHandlerTest.
 */
class PersonHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('people');
        $this->clearTable('organizations');
        $this->clearTable('usergroups');

        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\Person());
        $this->assertTrue($this->loggerHandler->hasErrorRecords());
    }

    public function test_create_and_update_entity()
    {
        $model = $this->createBaseModel();
        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity    = $this->getBaseEntity();
        $importMap = $this->em()->getRepository(Entity\ImportMap::class)->findOneBy([
            'typename' => $this->get('dp.importer.writer.mapper.import_map')->getImportMapKey($model),
            'old_id'   => 1,
        ]);

        $this->assertNotNull($entity);
        $this->assertNotNull($importMap);
        $this->assertEquals($entity->getId(), $importMap->getNewId());

        $this->em()->clear();
        $model->setName('name_updated');
        $this->writer->writeModel($model);
        $this->em()->clear();

        $entityUpdated = $this->getBaseEntity();

        $this->assertNotNull($entityUpdated);
        $this->assertEquals('name_updated', $entityUpdated->getName());
        $this->assertEquals($entity->getId(), $entityUpdated->getId());
    }

    public function test_check_props()
    {
        $customField1 = new Model\CustomField();
        $customField1->setOid(1);
        $customField1->setValue('custom val');

        $websiteModel1 = new Model\ContactData\Website();
        $websiteModel1->setComment('comment');
        $websiteModel1->setUrl('http://url.com');

        $model = $this->createBaseModel();
        $model->setName('');
        $model->setFirstName('f_name');
        $model->setLastName('l_name');
        $model->setOverrideDisplayName('o_name');
        $model->setAsAgent(true);
        $model->setAsAdmin(true);
        $model->setAsDeleted(true);
        $model->setAsDisabled(true);
        $model->setTimezone('Europe/London');
        $model->setPassword('password');
        $model->setOrganization(2);
        $model->setOrganizationPosition('manager');
        $model->setEmails(['email@deskpro.dev', 'email2@deskpro.dev']);
        $model->setUserGroups(['Group 1', 'Group 2']);
        $model->setLabels(['label 1', 'label 2']);
        $model->addCustomField($customField1);
        $model->getContactData()->addWebsite($websiteModel1);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity('email@deskpro.dev');

        $this->assertNotNull($entity);
        $this->assertEquals('f_name l_name', $entity->getName());
        $this->assertEquals('f_name', $entity->getFirstName());
        $this->assertEquals('l_name', $entity->getLastName());
        $this->assertEquals('email@deskpro.dev', $entity->getPrimaryEmailAddress());
        $this->assertTrue($entity->isAgent());
        $this->assertTrue($entity->isAdmin());
        $this->assertTrue($entity->isDeleted());
        $this->assertTrue($entity->isDisabled());
        $this->assertEquals('Europe/London', $entity->getTimezone());
        $this->assertNotNull($entity->getPassword());
        $this->assertEquals('Organization2', $entity->getOrganization()->getName());
        $this->assertEquals('manager', $entity->getOrganizationPosition());
        $this->assertEquals(['email@deskpro.dev', 'email2@deskpro.dev'], $entity->getEmailAddresses());
        $this->assertCount(2, $entity->getUsergroups());
        $this->assertEmpty($entity->getUsergroups()[0]->getSysName());
        $this->assertEmpty($entity->getUsergroups()[1]->getSysName());
        $this->assertCount(2, $entity->getLabels());
        $this->assertCount(1, $entity->getCustomData());
        $this->assertCount(1, $entity->getContactData());
    }

    public function test_edit_emails()
    {
        $model = $this->createBaseModel();
        $model->setEmails(['email1@deskpro.dev', 'email2@deskpro.dev']);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity('email2@deskpro.dev');
        $this->assertEquals(['email1@deskpro.dev', 'email2@deskpro.dev'], $entity->getEmailAddresses());

        $model->setEmails(['email2@deskpro.dev', 'email3@deskpro.dev', 'email4@deskpro.dev']);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity('email2@deskpro.dev');
        $this->assertEquals(['email2@deskpro.dev', 'email3@deskpro.dev', 'email4@deskpro.dev'], $entity->getEmailAddresses());
    }

    public function test_name_email_fallback()
    {
        $model = new Model\Person();
        $model->setOid(1);
        $model->addEmail('user@deskpro.dev');

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertEquals('User', $entity->getName());
    }

    /**
     * @return Model\Person
     */
    private function createBaseModel()
    {
        $model = new Model\Person();
        $model->setOid(1);
        $model->setName('name');
        $model->addEmail('user@deskpro.dev');

        return $model;
    }

    /**
     * @param string $email
     *
     * @return Entity\Person
     */
    private function getBaseEntity($email = 'user@deskpro.dev')
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $repo */
        $repo = $this->em()->getRepository(Entity\Person::class);

        return $repo->findOneByEmail($email);
    }
}
