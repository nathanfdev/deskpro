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
