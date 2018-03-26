<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\PersonHelper;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class PersonHelperTest.
 */
class PersonHelperTest extends AbstractWriterTest
{
    /**
     * @var PersonHelper
     */
    private $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->helper = $this->getContainer()->get('dp.importer.writer.helper.person');
        $this->clearTable('people');

        parent::setUp();
    }

    public function test_import_new_person_by_oid()
    {
        $person = $this->helper->findOrCreatePerson(1);

        $this->assertNotNull($person->getId());
        $this->assertEquals('imported.user.1@example.com', $person->getEmailAddress());
    }

    public function test_import_new_person_by_email()
    {
        $person = $this->helper->findOrCreatePerson('unknown_email@deskpro.dev');

        $this->assertNotNull($person->getId());
        $this->assertEquals('unknown_email@deskpro.dev', $person->getEmailAddress());
    }

    public function test_existing_person_by_email()
    {
        $person = new Person();
        $person->setName('name');
        $person->addEmailAddressString('email@deskpro.dev');

        $this->em()->persist($person);
        $this->em()->flush();

        $helperPerson = $this->helper->findOrCreatePerson('email@deskpro.dev');

        $this->assertEquals($person->getId(), $helperPerson->getId());
        $this->assertEquals('email@deskpro.dev', $helperPerson->getEmailAddress());
    }

    public function test_existing_person_by_oid()
    {
        $person = new Person();
        $person->setName('name');
        $person->addEmailAddressString('email@deskpro.dev');

        $this->em()->persist($person);
        $this->em()->flush();

        $personModel = new Model\Person();
        $personModel->setOid(5);

        $this->getContainer()->get('dp.importer.writer.mapper.import_map')->saveMapping($personModel, $person);

        $helperPerson = $this->helper->findOrCreatePerson(5);

        $this->assertEquals($person->getId(), $helperPerson->getId());
        $this->assertEquals('email@deskpro.dev', $person->getEmailAddress());
    }
}
