<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPhoneNumber;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Phone;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Website;
use DeskPRO\Bundle\ImportBundle\Model\Person as PersonModel;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\PersonContactDataHelper;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class PersonContactDataHelperTest.
 */
class PersonContactDataHelperTest extends AbstractWriterTest
{
    /**
     * @var PersonContactDataHelper
     */
    private $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->helper = $this->getContainer()->get('dp.importer.writer.helper.person_contact_data');
        $this->clearTable('people');

        parent::setUp();
    }

    public function test_contact_data_prevent_dupes()
    {
        $entity = new Person();
        $this->em()->persist($entity);
        $this->em()->flush();

        $oid   = $entity->getId();
        $model = new PersonModel();
        $phone = new Website();
        $phone->setUrl('http://deskpro.dev/');

        $contactData = $model->getContactData();
        $contactData->addWebsite($phone);

        $this->helper->updateContactData($model, $entity);
        $this->helper->updateContactData($model, $entity);

        $this->em()->clear(Person::class);
        $entity = $this->em()->getRepository(Person::class)->find($oid);

        $this->assertCount(1, $entity->getContactData());
    }

    public function test_set_phone_number()
    {
        $entity = new Person();
        $this->em()->persist($entity);
        $this->em()->flush();

        $oid   = $entity->getId();
        $model = new PersonModel();
        $phone = new Phone();
        $phone->setNumber('+14157012311');

        $contactData = $model->getContactData();
        $contactData->addPhone($phone);

        $this->helper->updateContactData($model, $entity);

        $this->em()->clear(Person::class);
        $entity = $this->em()->getRepository(Person::class)->find($oid);

        $this->assertCount(1, $entity->getPhoneNumbers());
        $this->assertEquals('+14157012311', $entity->getPhoneNumbers()[0]->getNumber());
    }

    public function test_add_phone_number()
    {
        $phoneNumber = new PersonPhoneNumber();
        $phoneNumber->setNumber('+14157012310');
        $phoneNumber->setRegion('');
        $phoneNumber->setGuessedType('phone');

        $entity = new Person();
        $entity->getPhoneNumbers()->add($phoneNumber);
        $phoneNumber->setPerson($entity);

        $this->em()->persist($entity);
        $this->em()->flush();

        $oid   = $entity->getId();
        $model = new PersonModel();
        $phone = new Phone();
        $phone->setNumber('+14157012311');

        $contactData = $model->getContactData();
        $contactData->addPhone($phone);

        $this->helper->updateContactData($model, $entity);

        $this->em()->clear(Person::class);
        $entity = $this->em()->getRepository(Person::class)->find($oid);

        $this->assertCount(2, $entity->getPhoneNumbers());
        $this->assertEquals('+14157012310', $entity->getPhoneNumbers()[0]->getNumber());
        $this->assertEquals('+14157012311', $entity->getPhoneNumbers()[1]->getNumber());
    }

    public function test_update_phone_number()
    {
        $phoneNumber = new PersonPhoneNumber();
        $phoneNumber->setNumber('+14157012311');
        $phoneNumber->setRegion('');
        $phoneNumber->setGuessedType('phone');

        $entity = new Person();
        $entity->getPhoneNumbers()->add($phoneNumber);
        $phoneNumber->setPerson($entity);

        $this->em()->persist($entity);
        $this->em()->flush();

        $oid   = $entity->getId();
        $model = new PersonModel();
        $phone = new Phone();
        $phone->setNumber('+14157012311');

        $contactData = $model->getContactData();
        $contactData->addPhone($phone);

        $this->helper->updateContactData($model, $entity);

        $this->em()->clear(Person::class);
        $entity = $this->em()->getRepository(Person::class)->find($oid);

        $this->assertCount(1, $entity->getPhoneNumbers());
    }
}
