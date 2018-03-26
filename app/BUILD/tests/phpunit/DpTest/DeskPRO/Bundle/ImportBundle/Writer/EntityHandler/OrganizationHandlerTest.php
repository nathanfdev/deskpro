<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class OrganizationHandlerTest.
 */
class OrganizationHandlerTest extends AbstractEntityHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->clearTable('organizations');
        parent::setUp();
    }

    public function test_validation()
    {
        $this->writer->writeModel(new Model\Organization());
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

        $entityUpdated = $this->getBaseEntity('name_updated');

        $this->assertNotNull($entityUpdated);
        $this->assertEquals($entity->getId(), $entityUpdated->getId());
    }

    public function test_check_props()
    {
        $blobModel = new Model\Blob();
        $blobModel->setBlobData('blob data');
        $blobModel->setContentType('text/plain');
        $blobModel->setFileName('file1.txt');

        $customField1 = new Model\CustomField();
        $customField1->setOid(1);
        $customField1->setValue('custom val');

        $model = $this->createBaseModel();
        $model->setImportance(5);
        $model->setDateCreated(new \DateTime('2016-07-10'));
        $model->setLabels(['label 1', 'label 2']);
        $model->setPicture($blobModel);
        $model->addCustomField($customField1);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertEquals('name', $entity->getName());
        $this->assertEquals(5, $entity->getImportance());
        $this->assertEquals('2016-07-10', $entity->getDateCreated()->format('Y-m-d'));
        $this->assertEquals('file1.txt', $entity->getPicture()->getFilename());
        $this->assertEquals('label 1', $entity->getLabels()[0]->getLabel());
        $this->assertEquals('label 2', $entity->getLabels()[1]->getLabel());
        $this->assertCount(1, $entity->getCustomData());
        $this->assertEquals('custom val', $entity->getCustomData()[0]->getInput());
    }

    public function test_create_organization_with_contact_data()
    {
        $addressModel = new Model\ContactData\Address();
        $addressModel->setComment('comment');
        $addressModel->setAddress('address');
        $addressModel->setCity('city');
        $addressModel->setCountry('country');
        $addressModel->setState('state');
        $addressModel->setZip('zip');

        $facebookModel = new Model\ContactData\Facebook();
        $facebookModel->setComment('comment');
        $facebookModel->setUrl('http://facebook.com/profile');

        $instantMessageModel = new Model\ContactData\InstantMessage();
        $instantMessageModel->setComment('comment');
        $instantMessageModel->setUsername('username');
        $instantMessageModel->setService('icq');

        $linkedInModel = new Model\ContactData\LinkedIn();
        $linkedInModel->setComment('comment');
        $linkedInModel->setUrl('http://url/in/');

        $phoneModel = new Model\ContactData\Phone();
        $phoneModel->setComment('comment');
        $phoneModel->setNumber('+14157012311');
        $phoneModel->setType('fax');

        $twitterModel = new Model\ContactData\Twitter();
        $twitterModel->setComment('comment');
        $twitterModel->setUsername('username');
        $twitterModel->setDisplayFeed(1);

        $websiteModel = new Model\ContactData\Website();
        $websiteModel->setComment('comment');
        $websiteModel->setUrl('http://url');

        $model = $this->createBaseModel();
        $model->getContactData()->addAddress($addressModel);
        $model->getContactData()->addFacebook($facebookModel);
        $model->getContactData()->addInstantMessage($instantMessageModel);
        $model->getContactData()->addLinkedIn($linkedInModel);
        $model->getContactData()->addPhone($phoneModel);
        $model->getContactData()->addTwitter($twitterModel);
        $model->getContactData()->addWebsite($websiteModel);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertCount(7, $entity->getContactData());

        /** @var Entity\ContactDataAbstract[] $contactData */
        $contactData = array_values($entity->getContactData()->toArray());

        $this->assertEquals('address', $contactData[0]->getContactType());
        $this->assertEquals('comment', $contactData[0]->getComment());
        $this->assertEquals('address', $contactData[0]->getField1());
        $this->assertEquals('city', $contactData[0]->getField2());
        $this->assertEquals('state', $contactData[0]->getField3());
        $this->assertEquals('zip', $contactData[0]->getField4());
        $this->assertEquals('country', $contactData[0]->getField5());

        $this->assertEquals('facebook', $contactData[1]->getContactType());
        $this->assertEquals('comment', $contactData[1]->getComment());
        $this->assertEquals('http://facebook.com/profile', $contactData[1]->getField1());

        $this->assertEquals('instant_message', $contactData[2]->getContactType());
        $this->assertEquals('comment', $contactData[2]->getComment());
        $this->assertEquals('username', $contactData[2]->getField1());
        $this->assertEquals('icq', $contactData[2]->getField2());

        $this->assertEquals('linked_in', $contactData[3]->getContactType());
        $this->assertEquals('comment', $contactData[3]->getComment());
        $this->assertEquals('http://url/in/', $contactData[3]->getField1());

        $this->assertEquals('phone', $contactData[4]->getContactType());
        $this->assertEquals('comment', $contactData[4]->getComment());
        $this->assertEquals('1', $contactData[4]->getField1());
        $this->assertEquals('4157012311', $contactData[4]->getField2());
        $this->assertEquals('fax', $contactData[4]->getField3());

        $this->assertEquals('twitter', $contactData[5]->getContactType());
        $this->assertEquals('comment', $contactData[5]->getComment());
        $this->assertEquals('username', $contactData[5]->getField1());
        $this->assertEquals(1, $contactData[5]->getField2());

        $this->assertEquals('website', $contactData[6]->getContactType());
        $this->assertEquals('comment', $contactData[6]->getComment());
        $this->assertEquals('http://url', $contactData[6]->getField1());
    }

    public function test_update_contact_data_by_oid()
    {
        $websiteModel = new Model\ContactData\Website();
        $websiteModel->setOid(1);
        $websiteModel->setComment('comment');
        $websiteModel->setUrl('http://url');

        $model = $this->createBaseModel();
        $model->getContactData()->addWebsite($websiteModel);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->getContactData());

        $websiteModel->setComment('comment_updated');
        $websiteModel->setUrl('http://urlupdated');

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();

        $this->assertNotNull($entity);
        $this->assertCount(1, $entity->getContactData());
        $this->assertEquals('comment_updated', $entity->getContactData()->first()->getComment());
        $this->assertEquals('http://urlupdated', $entity->getContactData()->first()->getField1());
    }

    public function test_add_email_domain()
    {
        // base create
        $model = $this->createBaseModel();
        $model->setEmailDomains(['domain.com']);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertCount(1, $entity->getEmailDomains());
        $this->assertEquals('domain.com', $entity->getEmailDomains()->first()->getDomain());

        // append
        $model->setEmailDomains(['domain2.com']);

        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertCount(2, $entity->getEmailDomains());
        $this->assertEquals('domain.com', $entity->getEmailDomains()->first()->getDomain());
        $this->assertEquals('domain2.com', $entity->getEmailDomains()->last()->getDomain());

        // duplicate check
        $this->writer->writeModel($model);
        $this->em()->clear();

        $entity = $this->getBaseEntity();
        $this->assertCount(2, $entity->getEmailDomains());
        $this->assertEquals('domain.com', $entity->getEmailDomains()->first()->getDomain());
        $this->assertEquals('domain2.com', $entity->getEmailDomains()->last()->getDomain());
    }

    /**
     * @return Model\Organization
     */
    private function createBaseModel()
    {
        $model = new Model\Organization();
        $model->setOid(1);
        $model->setName('name');

        return $model;
    }

    /**
     * @param string $name
     *
     * @return Entity\Organization
     */
    private function getBaseEntity($name = 'name')
    {
        return $this->em()->getRepository(Entity\Organization::class)->findOneBy([
            'name' => $name,
        ]);
    }
}
