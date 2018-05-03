<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\EmailBundle\Entity\SendmailSource;
use DeskPRO\Bundle\ImportBundle\CsvImport\CsvImporter;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class CsvImporterTest.
 */
class CsvImportPersonTest extends AbstractWriterTest
{
    /**
     * @var CsvImporter
     */
    private $importer;

    public function setUp()
    {
        $this->clearTable('people_emails');
        $this->clearTable('people');
        $this->clearTable('organizations');
        $this->clearTable('sendmail_sources');

        parent::setUp();

        $this->importer = $this->getContainer()->get('dp.importer.csv');
    }

    public function test_name()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
            ],
            ['New Person', 'new-person@example.com'],
            'ref'
        ));

        $this->assertEquals('new-person@example.com', $this->getPerson()->getEmailAddress());
        $this->assertEquals('New Person', $this->getPerson()->getName());
    }

    public function test_first_and_last_name()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'first_name'],
                ['map' => 'last_name'],
                ['map' => 'primary_email'],
            ],
            ['My', 'Name', 'new-person@example.com'],
            'ref'
        ));

        $this->assertEquals('My Name', $this->getPerson()->getName());
    }

    public function test_secondary_email()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'secondary_email'],
            ],
            ['Name', 'new-person@example.com', 'new-person2@example.com'],
            'ref'
        ));

        $this->assertCount(2, $this->getPerson()->getEmailAddresses());
        $this->assertEquals('new-person@example.com', $this->getPerson()->getEmailAddresses()[0]);
        $this->assertEquals('new-person2@example.com', $this->getPerson()->getEmailAddresses()[1]);
    }

    public function test_title_prefix()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'title_prefix'],
                ['map' => 'primary_email'],
            ],
            ['New Person', 'Mr.', 'new-person@example.com'],
            'ref'
        ));

        $this->assertEquals('Mr.', $this->getPerson()->getTitlePrefix());
    }

    public function test_password()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'password'],
            ],
            ['New Person', 'new-person@example.com', 'my_password'],
            'ref'
        ));

        $this->assertTrue(password_verify('my_password', $this->getPerson()->getPassword()));
    }

    public function test_organization()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'organization'],
                ['map' => 'organization_position'],
            ],
            ['New Person', 'new-person@example.com', 'My Org', 'manager'],
            'ref'
        ));

        $this->assertEquals('My Org', $this->getPerson()->getOrganization()->getName());
        $this->assertEquals('manager', $this->getPerson()->getOrganizationPosition());
    }

    public function test_language_by_code()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'language'],
            ],
            ['New Person', 'new-person@example.com', 'eng'],
            'ref'
        ));

        $this->assertEquals('eng', $this->getPerson()->getLanguage()->getLangCode());
    }

    public function test_language_by_id()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'language'],
            ],
            ['New Person', 'new-person@example.com', $this->em()->getRepository(Language::class)->findOneBy([])->getId()],
            'ref'
        ));

        $this->assertEquals('eng', $this->getPerson()->getLanguage()->getLangCode());
    }

    /**
     * @dataProvider simpleContactDataProvider
     *
     * @param string $type
     * @param string $field1
     * @param string $field2
     * @param string $field3
     * @param string $value
     */
    public function test_simple_contact_data($type, $field1, $field2, $field3, $value = null)
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => $type],
            ],
            ['New Person', 'new-person@example.com', $value ?: $field1],
            'ref'
        ));

        /** @var ContactDataAbstract[] $contactData */
        $contactData = array_values($this->getPerson()->getContactData()->toArray());
        $this->assertEquals($field1, $contactData[0]->getField1());
        $this->assertEquals($field2, $contactData[0]->getField2());
        $this->assertEquals($field3, $contactData[0]->getField3());
    }

    /**
     * @return array
     */
    public function simpleContactDataProvider()
    {
        return [
            ['website', 'http://mysite.com', null, null],
            ['twitter', 'my_username', '0', null],
            ['facebook', 'http://facebook.com/username', 'username', null],
            ['linkedin', 'http://linkedin.com/in/profile', 'profile', null],
            ['im', 'username', 'aim', null],
        ];
    }

    public function test_im_type()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'im', 'type' => 'skype'],
            ],
            ['New Person', 'new-person@example.com', 'username'],
            'ref'
        ));

        /** @var ContactDataAbstract[] $contactData */
        $contactData = array_values($this->getPerson()->getContactData()->toArray());
        $this->assertEquals('username', $contactData[0]->getField1());
        $this->assertEquals('skype', $contactData[0]->getField2());
    }

    public function test_phone_type()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'phone', 'type' => 'mobile'],
            ],
            ['New Person', 'new-person@example.com', '+14157012311'],
            'ref'
        ));

        /** @var PhoneNumber[] $contactData */
        $contactData = array_values($this->getPerson()->getPhoneNumbers()->toArray());
        $this->assertEquals('+14157012311', $contactData[0]->getNumber());
        $this->assertEquals('mobile', $contactData[0]->getGuessedType());
    }

    public function test_address()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'address'],
                ['map' => 'city'],
                ['map' => 'country'],
            ],
            ['New Person', 'new-person@example.com', 'some address', 'some city', 'some country'],
            'ref'
        ));

        /** @var ContactDataAbstract[] $contactData */
        $contactData = array_values($this->getPerson()->getContactData()->toArray());
        $this->assertEquals('some address', $contactData[0]->getField1());
        $this->assertEquals('some city', $contactData[0]->getField2());
        $this->assertEquals('some country', $contactData[0]->getField5());
    }

    /**
     * @dataProvider addressPartsProvider
     *
     * @param string $field
     */
    public function test_address_parts($field)
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => $field],
            ],
            ['New Person', 'new-person@example.com', 'some address'],
            'ref'
        ));
    }

    /**
     * @return array
     */
    public function addressPartsProvider()
    {
        return [
            ['address'],
            ['address1'],
            ['address2'],
            ['city'],
            ['country'],
        ];
    }

    public function test_custom_field_by_name()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'new_custom', 'title' => 'Field 1'],
            ],
            ['New Person', 'new-person@example.com', 'custom field value'],
            'ref'
        ));

        /** @var CustomDataAbstract[] $customFields */
        $customFields = array_values($this->getPerson()->getCustomData()->toArray());
        $this->assertEquals('custom field value', $customFields[0]->getInput());
    }

    public function test_custom_field_by_id()
    {
        $customDef = new CustomDefPerson();
        $customDef->setWidgetType('textarea');
        $customDef->setTitle('Custom def');

        $this->em()->persist($customDef);
        $this->em()->flush();

        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
                ['map' => 'custom_'.$customDef->getId()],
            ],
            ['New Person', 'new-person@example.com', 'custom field value'],
            'ref'
        ));

        /** @var CustomDataAbstract[] $customFields */
        $customFields = array_values($this->getPerson()->getCustomData()->toArray());
        $this->assertEquals('custom field value', $customFields[0]->getInput());
    }

    public function test_welcome_email_for_new_user()
    {
        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
            ],
            ['New Person', 'new-person@example.com'],
            'ref'
        ));

        $this->assertCount(1, $this->em()->getRepository(SendmailSource::class)->findAll());
    }

    public function test_test_no_welcome_email_for_updated_user()
    {
        $person = new Person();
        $person->setEmail('existing-person@example.com');

        $this->em()->persist($person);
        $this->em()->flush();

        $this->assertInternalType('integer', $this->importer->importPerson(
            [
                ['map' => 'name'],
                ['map' => 'primary_email'],
            ],
            ['New Person', 'existing-person@example.com'],
            'ref'
        ));

        $this->assertCount(0, $this->em()->getRepository(SendmailSource::class)->findAll());
    }

    /**
     * @return Person
     */
    private function getPerson()
    {
        return $this->em()->getRepository(Person::class)->findOneBy([]);
    }
}
