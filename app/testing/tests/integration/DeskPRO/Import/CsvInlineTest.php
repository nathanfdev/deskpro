<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DpIntegrationTests\DeskPRO\Import;

use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Command\ImportBatchCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CsvInlineTest.
 *
 * @group importer
 */
class CsvInlineTest extends \DpIntegrationTestCase
{
    /**
     * @var string
     */
    private $input_path;

    /**
     * @var string
     */
    private $output_path;

    /**
     * @var EntityRepository\Person
     */
    private $person_repository;

    /**
     * @var EntityRepository\Organization
     */
    private $organization_repository;

    /**
     * {@inheritdoc}
     */
    public function runBefore()
    {
        $this->helper->enableFreshDatabaseSet('FreshDb');

        $this->helper->loadFixtures('Import/CustomDefTicket');
        $this->helper->loadFixtures('Import/CustomDefPerson');
        $this->helper->loadFixtures('Import/CustomDefFeedback');
        $this->helper->loadFixtures('Import/CustomDefArticle');
        $this->helper->loadFixtures('Import/CustomDefOrganization');

        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $entity_manager->clear();

        $this->person_repository       = $entity_manager->getRepository('DeskPRO:Person');
        $this->organization_repository = $entity_manager->getRepository('DeskPRO:Organization');

        $this->input_path  = DP_ROOT.'/src/Application/ImportBundle/Resources/example/csv_inline';
        $this->output_path = dp_get_data_dir().'/import/csv/export';

        if (!is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);

        $this->overrideDpRootPath('/organizations.csv');
    }

    public function testImportBatch()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ImportBatchCommand());

        $command        = $application->find('dp:import:batch');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'csv',
            '--input-path'  => $this->input_path,
            '--output-path' => $this->output_path,
            '--batch'       => true,
            '--verbose'     => true,
        ));

        $this->checkJsonData($command_tester);
        $this->checkDbData();
    }

    /**
     * @param CommandTester $command_tester
     */
    private function checkJsonData(CommandTester $command_tester)
    {
        $output = $command_tester->getDisplay();

        // Checking for people
        $this->assertContains('[CSVPerson #num_1 (person_angry.customer@example.com)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVPerson #num_2 (person_some@email.tld)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVPerson #num_3 (person_another@email.tld)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #0 (person_joe.smith@example.com)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #1 (person_joe.smith@example.com)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #2 (person_joe.smith@example.com)] Entity parsed successfully!', $output);

        $this->helper->seeFileFound('1/people/person_some@email.tld.json');
        $this->helper->seeInThisFile('Some Customer');
        $this->helper->seeInThisFile('"is_agent":false');
        $this->helper->seeInThisFile('"is_admin":false');

        $this->helper->seeFileFound('1/people/person_joe.smith@example.com.json');
        $this->helper->seeInThisFile('Joe Smith');
        $this->helper->seeInThisFile('Joe Smith');

        $person = $this->getContent('1/people/person_joe.smith@example.com.json');
        $this->assertCount(5, $person['custom_fields']);
        $this->assertCount(4, $person['contact_data']);

        $person = $this->getContent('1/people/person_angry.customer@example.com.json');
        $this->assertCount(2, $person['custom_fields']);
        $this->assertCount(4, $person['contact_data']);

        // Checking for tickets
        $this->assertContains('[CSVTicket #144 (ticket_144)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVTicket #145 (ticket_145)] Entity parsed successfully!', $output);

        $ticket = $this->getContent('1/tickets/ticket_144.json');
        $this->assertCount(4, $ticket['custom_fields']);

        $ticket = $this->getContent('1/tickets/ticket_145.json');
        $this->assertCount(5, $ticket['custom_fields']);

        // Checking for articles
        $this->assertContains('[CSVArticle #num_0 (article_num_0)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVArticle #num_1 (article_num_1)] Entity parsed successfully!', $output);

        $article = $this->getContent('1/articles/article_num_0.json');
        $this->assertArrayHasKey('import_map_key', $article);
        $this->assertNull($article['import_map_key']);
        $this->assertCount(2, $article['custom_fields']);

        $article = $this->getContent('1/articles/article_num_1.json');
        $this->assertCount(2, $article['custom_fields']);

        // Checking for feedback
        $this->assertContains('[CSVFeedback #1 (feedback_1)] Entity parsed successfully!', $output);

        $feedback = $this->getContent('1/feedback/feedback_1.json');
        $this->assertCount(2, $feedback['custom_fields']);

        // Checking for organizations
        $this->assertContains('[CSVOrganization #num_0 (organization_some_organization)] Entity parsed successfully!', $output);

        $organization = $this->getContent('1/organizations/organization_some_organization.json');
        $this->assertCount(2, $organization['custom_fields']);
        $this->assertCount(6, $organization['contact_data']);
    }

    private function checkDbData()
    {
        $this->checkDbPeopleData();
        $this->checkDbOrganizationData();
    }

    private function checkDbPeopleData()
    {
        $person = $this->person_repository->findOneByEmail('joe.smith@example.com');
        $this->assertNotNull($person);

        $contact_data1 = $person->getContactData('facebook');
        $this->assertCount(1, $contact_data1);

        $contact = $contact_data1[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('http://facebook.com/facebook_id', $contact->getField1());

        $contact_data2 = $person->getContactData('instant_message');
        $this->assertCount(1, $contact_data2);

        $contact = $contact_data2[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('im_id', $contact->getField1());

        $contact_data3 = $person->getContactData('phone');
        $this->assertCount(1, $contact_data3);

        $contact = $contact_data3[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('JE', $contact->getField1());
        $this->assertEquals('+447700900315', $contact->getField2());
        $this->assertEquals('mobile', $contact->getField3());

        $contact_data4 = $person->getContactData('linked_in');
        $this->assertCount(1, $contact_data4);

        $contact = $contact_data4[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('linkedin_url', $contact->getField1());
    }

    private function checkDbOrganizationData()
    {
        /** @var Entity\Organization $organization */
        $organization = $this->organization_repository->findOneBy(array('name' => 'some organization'));
        $this->assertNotNull($organization);

        $contact_data1 = $organization->getContactData('twitter');
        $this->assertCount(1, $contact_data1);

        $contact = $contact_data1[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('twitter_acc_id', $contact->getField1());

        $contact_data2 = $organization->getContactData('phone');
        $this->assertCount(1, $contact_data2);

        $contact = $contact_data2[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('JE', $contact->getField1());
        $this->assertEquals('+447700900315', $contact->getField2());
        $this->assertEquals('mobile', $contact->getField3());

        $contact_data3 = $organization->getContactData('fax');
        $this->assertCount(1, $contact_data3);

        $contact = $contact_data3[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('US', $contact->getField1());
        $this->assertEquals('+12025550156', $contact->getField2());
        $this->assertEquals('landline-or-mobile', $contact->getField3());

        $contact_data4 = $organization->getContactData('skype');
        $this->assertCount(1, $contact_data4);

        $contact = $contact_data4[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('skype_username', $contact->getField1());

        $contact_data5 = $organization->getContactData('website');
        $this->assertCount(1, $contact_data5);

        $contact = $contact_data5[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('http://site.com', $contact->getField1());

        $contact_data6 = $organization->getContactData('address');
        $this->assertCount(1, $contact_data6);

        $contact = $contact_data6[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('1st street', $contact->getField1());
        $this->assertEquals('London', $contact->getField2());
        $this->assertEquals('CA', $contact->getField3());
        $this->assertEquals('10587563456', $contact->getField4());
        $this->assertEquals('UK', $contact->getField5());
    }

    /**
     * @param string $filename
     *
     * @return array
     */
    private function getContent($filename)
    {
        return json_decode(file_get_contents($filename), true);
    }

    /**
     * @param string $file
     */
    private function overrideDpRootPath($file)
    {
        $dp_root = str_replace('/app', '/', DP_ROOT);

        $content = file_get_contents($this->input_path.$file);
        $content = str_replace('/deskpro/www/', $dp_root, $content);

        file_put_contents($this->input_path.$file, $content);
    }
}
