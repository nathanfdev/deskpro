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
use Application\ImportBundle\Command\CheckExportCommand;
use Application\ImportBundle\Command\ExportCommand;
use Application\ImportBundle\Command\ImportBatchCommand;
use Application\ImportBundle\Command\ImportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ImportCsvTest.
 *
 * @group importer
 */
class CsvTest extends \DpIntegrationTestCase
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
     * @var EntityRepository\Ticket
     */
    private $ticket_repository;

    /**
     * @var EntityRepository\TicketAttachment
     */
    private $ticket_attachment_repository;

    /**
     * @var EntityRepository\Person
     */
    private $person_repository;

    /**
     * @var EntityRepository\News
     */
    private $news_repository;

    /**
     * @var EntityRepository\Article
     */
    private $article_repository;

    /**
     * @var EntityRepository\ArticleCategory
     */
    private $article_category_repository;

    /**
     * @var EntityRepository\Feedback
     */
    private $feedback_repository;

    /**
     * @var EntityRepository\FeedbackAttachment
     */
    private $feedback_attachment_repository;

    /**
     * @var EntityRepository\Download
     */
    private $download_repository;

    /**
     * @var EntityRepository\Organization
     */
    private $organization_repository;

    /**
     * @var \Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    private $organization_contact_data_repository;

    /**
     * @var EntityRepository\Blob
     */
    private $blob_repository;

    /**
     * @var EntityRepository\CustomDataTicket
     */
    private $custom_data_ticket_repository;

    /**
     * @var \Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    private $custom_data_person_repository;

    /**
     * @var \Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    private $custom_data_feedback_repository;

    /**
     * @var \Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    private $custom_data_article_repository;

    /**
     * @var \Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    private $custom_data_organization_repository;

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

        $this->ticket_repository                    = $entity_manager->getRepository('DeskPRO:Ticket');
        $this->ticket_attachment_repository         = $entity_manager->getRepository('DeskPRO:TicketAttachment');
        $this->person_repository                    = $entity_manager->getRepository('DeskPRO:Person');
        $this->news_repository                      = $entity_manager->getRepository('DeskPRO:News');
        $this->article_repository                   = $entity_manager->getRepository('DeskPRO:Article');
        $this->article_category_repository          = $entity_manager->getRepository('DeskPRO:ArticleCategory');
        $this->feedback_repository                  = $entity_manager->getRepository('DeskPRO:Feedback');
        $this->feedback_attachment_repository       = $entity_manager->getRepository('DeskPRO:FeedbackAttachment');
        $this->download_repository                  = $entity_manager->getRepository('DeskPRO:Download');
        $this->organization_repository              = $entity_manager->getRepository('DeskPRO:Organization');
        $this->organization_contact_data_repository = $entity_manager->getRepository('DeskPRO:OrganizationContactData');
        $this->blob_repository                      = $entity_manager->getRepository('DeskPRO:Blob');
        $this->custom_data_ticket_repository        = $entity_manager->getRepository('DeskPRO:CustomDataTicket');
        $this->custom_data_person_repository        = $entity_manager->getRepository('DeskPRO:CustomDataPerson');
        $this->custom_data_feedback_repository      = $entity_manager->getRepository('DeskPRO:CustomDataFeedback');
        $this->custom_data_article_repository       = $entity_manager->getRepository('DeskPRO:CustomDataArticle');
        $this->custom_data_organization_repository  = $entity_manager->getRepository('DeskPRO:CustomDataOrganization');

        $this->input_path  = DP_ROOT.'/src/Application/ImportBundle/Resources/example/csv';
        $this->output_path = dp_get_data_dir().'/import/csv/export';

        if (!is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);

        $this->overrideDpRootPath('/ticket_attachments.csv');
        $this->overrideDpRootPath('/feedback_attachments.csv');
        $this->overrideDpRootPath('/downloads.csv');
        $this->overrideDpRootPath('/organizations.csv');

        $this->checkDbEmpty();
        $this->checkJsonEmpty();
    }

    public function testCheck()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new CheckExportCommand());

        $command        = $application->find('dp:export:check');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'csv',
            '--input-path' => $this->input_path,
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $output = $command_tester->getDisplay();

        // Checking for tickets
        $this->assertContains('[CSVAttachment #0 (message_1)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVTicketMessage #1 (ticket_144)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVTicket #144 (ticket_144)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVTicket #145 (ticket_145)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #0 (ticket_144)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #1 (ticket_144)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #2 (ticket_145)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #3 (ticket_145)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #4 (ticket_145)] Entity parsed successfully!', $output);

        // Checking for people
        $this->assertContains('[CSVPerson #1 (person_1)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVPerson #6 (person_6)] Entity parsed successfully!', $output);

        // Checking for article categories
        $this->assertContains('[CSVArticleCategory #num_0 (article_category_num_0)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVArticleCategory #num_1 (article_category_num_1)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVArticleCategory #num_2 (article_category_num_2)] Entity parsed successfully!', $output);

        $this->assertContains('[CSVArticle #1 (article_1)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVArticle #2 (article_2)] Entity parsed successfully!', $output);

        // Checking for downloads
        $this->assertContains('[CSVDownload #num_0 (download_num_0)] Entity parsed successfully!', $output);

        // Checking for feedback
        $this->assertContains('[CSVFeedback #1 (feedback_1)] Entity parsed successfully!', $output);
        $this->assertContains('CSVAttachment #0 (feedback_1)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #0 (feedback_1)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #1 (feedback_1)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVCustomField #2 (feedback_1)] Entity parsed successfully!', $output);

        // Checking for news
        $this->assertContains('[CSVNews #num_0 (news_num_0)] Entity parsed successfully!', $output);
        $this->assertContains('[CSVNews #num_1 (news_num_1)] Entity parsed successfully!', $output);

        // Checking for organizations
        $this->assertContains('[CSVOrganization #num_0 (organization_some_organization)] Entity parsed successfully!', $output);
        $this->assertContains('Done. Checking was successful.', $output);

        $this->checkDbEmpty();
        $this->checkJsonEmpty();
    }

    public function testExport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ExportCommand());

        $command        = $application->find('dp:export:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'csv',
            '--input-path'  => $this->input_path,
            '--output-path' => $this->output_path,
            '--batch'       => true,
            '--verbose'     => true,
        ));

        $this->checkDbEmpty();
        $this->checkJsonData();
    }

    public function testImport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ImportCommand());

        $command        = $application->find('dp:import:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'csv',
            '--input-path' => $this->input_path,
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $this->checkDbWriterOutput($command_tester);
        $this->checkDbData();
        $this->checkJsonEmpty();
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
            '--verbose'     => true,
            '--batch'       => true,
        ));

        $this->checkDbWriterOutput($command_tester);
        $this->checkDbData();
        $this->checkJsonData();
    }

    private function checkJsonEmpty()
    {
        $this->assertFalse(file_exists('1/articles/'));
        $this->assertFalse(file_exists('1/people/'));
        $this->assertFalse(file_exists('1/tickets/'));
        $this->assertFalse(file_exists('1/feedback/'));
        $this->assertFalse(file_exists('1/news/'));
        $this->assertFalse(file_exists('1/downloads/'));
    }

    private function checkJsonData()
    {
        $this->helper->seeFileFound('1/articles/article_1.json');
        $this->helper->seeInThisFile('Article 1');

        $article = $this->getContent('1/articles/article_1.json');
        $this->assertArrayHasKey('import_map_key', $article);
        $this->assertEquals(Entity\ImportMap::TYPE_CSV_ARTICLE, $article['import_map_key']);

        $this->helper->seeFileFound('1/feedback/feedback_1.json');
        $this->helper->seeInThisFile('Feedback 1');

        $this->helper->seeFileFound('1/people/person_3.json');
        $this->helper->seeInThisFile('Some Customer');

        $this->helper->seeFileFound('1/tickets/ticket_144.json');
        $this->helper->seeInThisFile('How to submit a ticket');
        $this->helper->seeInThisFile('Any update on my ticket yet?');
        $this->helper->seeInThisFile('Resources\/example\/csv\/tickets.csv');

        $this->helper->seeFileFound('1/tickets/ticket_145.json');
        $this->helper->seeInThisFile('Another Ticket');

        $this->helper->seeFileFound('1/news/news_num_0.json');
        $this->helper->seeInThisFile('News Title 1');

        $this->helper->seeFileFound('1/downloads/download_num_0.json');
        $this->helper->seeInThisFile('Download 1');

        $this->helper->seeFileFound('1/organizations/organization_some_organization.json');
        $this->helper->seeInThisFile('Some Organization');
    }

    private function checkDbEmpty()
    {
        $this->assertEquals(0, $this->ticket_repository->countAll());
        $this->assertEquals(0, $this->ticket_attachment_repository->countAll());
        $this->assertEquals(1, $this->person_repository->countAll());
        $this->assertEquals(1, $this->news_repository->countAll());
        $this->assertEquals(1, $this->article_repository->countAll());
        $this->assertEquals(1, $this->article_category_repository->countAll());
        $this->assertEquals(1, $this->feedback_repository->countAll());
        $this->assertEquals(0, $this->feedback_attachment_repository->countAll());
        $this->assertEquals(0, $this->download_repository->countAll());
        $this->assertEquals(0, $this->organization_repository->countAll());
        $this->assertEquals(0, $this->organization_contact_data_repository->countAll());
        $this->assertEquals(0, $this->blob_repository->countAll());
        $this->assertEquals(0, $this->custom_data_ticket_repository->countAll());
        $this->assertEquals(0, $this->custom_data_person_repository->countAll());
        $this->assertEquals(0, $this->custom_data_feedback_repository->countAll());
        $this->assertEquals(0, $this->custom_data_article_repository->countAll());
        $this->assertEquals(0, $this->custom_data_organization_repository->countAll());
    }

    private function checkDbData()
    {
        $this->checkDbArticleData();
        $this->checkDbArticleCategoryData();
        $this->checkDbPeopleData();
        $this->checkDbTicketsData();
        $this->checkDbFeedbackData();
        $this->checkDbOrganizationData();
        $this->checkDbBlobData();
        $this->checkDbNewsData();
    }

    private function checkDbNewsData()
    {
        $this->assertCount(3, $this->news_repository->findAll());
    }

    private function checkDbPeopleData()
    {
        $this->assertCount(8, $this->person_repository->findAll());
        $this->assertCount(2, $this->custom_data_person_repository->findAll());

        $person = $this->person_repository->findOneByEmail('joe.smith@example.com');
        $this->assertNotNull($person);

        $this->assertEquals('Joe Smith', $person->getDisplayName());
        $this->assertTrue($person->isAgent());

        $contact_data1 = $person->getContactData('mobile');
        $this->assertNotEmpty($contact_data1);

        $contact = $contact_data1[0];

        $this->assertEquals('some comment', $contact->getComment());
        $this->assertEquals('+7', $contact->getField1());
        $this->assertEquals('1234567', $contact->getField2());
        $this->assertEquals('phone', $contact->getField3());

        $person = $this->person_repository->findOneByEmail('angry.customer@example.com');
        $this->assertEquals('Angry Customer', $person->getDisplayName());
        $this->assertFalse($person->isAgent());
    }

    private function checkDbTicketsData()
    {
        $this->assertNotEmpty($this->ticket_repository->findOneBy(array(
            'subject' => 'How to submit a ticket',
        )));
        $this->assertNotEmpty($this->ticket_repository->findOneBy(array(
            'subject' => 'Another Ticket',
        )));
        $this->assertCount(2, $this->ticket_repository->findAll());
        $this->assertCount(1, $this->ticket_attachment_repository->findAll());
        $this->assertCount(4, $this->custom_data_ticket_repository->findAll());
    }

    private function checkDbFeedbackData()
    {
        $this->assertEquals(2, $this->feedback_repository->countAll());
        $this->assertEquals(1, $this->feedback_attachment_repository->countAll());
        $this->assertEquals(2, $this->custom_data_feedback_repository->countAll());
    }

    private function checkDbArticleData()
    {
        $this->assertEquals(3, $this->article_repository->countAll());
        $this->assertEquals(2, $this->custom_data_article_repository->countAll());

        /** @var Entity\Article $article */
        $article = $this->article_repository->findOneBy(array('title' => 'Article 1'));
        $this->assertNotNull($article);

        $this->assertEquals('some@email.tld', $article->getPerson()->getPrimaryEmail()->getEmail());
        $this->assertEquals('Content 1', $article->getContentPlain());
        $this->assertEquals('published', $article->getStatusCode());
        $this->assertEquals('Category 1', $article->getPrimaryCategory());
    }

    private function checkDbArticleCategoryData()
    {
        $this->assertEquals(5, $this->article_category_repository->countAll());

        $category_1 = $this->article_category_repository->findOneBy(array('title' => 'Category 1', 'parent' => null));
        $this->assertNotNull($category_1);

        $category_2 = $this->article_category_repository->findOneBy(array('title' => 'Sub Category 1', 'parent' => $category_1));
        $this->assertNotNull($category_2);

        $category_3 = $this->article_category_repository->findOneBy(array('title' => 'Sub Category 2', 'parent' => $category_2));
        $this->assertNotNull($category_3);

        $category_4 = $this->article_category_repository->findOneBy(array('title' => 'Sub Category 3', 'parent' => $category_3));
        $this->assertNotNull($category_4);
    }

    private function checkDbOrganizationData()
    {
        $this->assertEquals(1, $this->organization_repository->countAll());

        /** @var Entity\Organization $organization */
        $organization = $this->organization_repository->findOneBy(array('name' => 'Some Organization'));
        $this->assertNotNull($organization);

        $contact_data1 = $organization->getContactData('phone');
        $this->assertCount(1, $contact_data1);

        $contact = $contact_data1[0];
        $this->assertEquals('some comment', $contact->getComment());
        $this->assertEquals('+7', $contact->getField1());
        $this->assertEquals('1234567', $contact->getField2());
        $this->assertEquals('phone', $contact->getField3());

        $contact_data2 = $organization->getContactData('fax');
        $this->assertCount(1, $contact_data2);

        $contact = $contact_data2[0];
        $this->assertEquals('', $contact->getComment());
        $this->assertEquals('US', $contact->getField1());
        $this->assertEquals('+12025550156', $contact->getField2());
        $this->assertEquals('landline-or-mobile', $contact->getField3());

        $contact_data3 = $organization->getContactData('mobile');
        $this->assertCount(0, $contact_data3);

        $this->assertEquals(4, $this->custom_data_organization_repository->countAll());
    }

    private function checkDbBlobData()
    {
        $this->assertCount(4, $this->blob_repository->findBy(array('content_type' => 'csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'downloads.csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'tickets.csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'organizations.csv')));
    }

    /**
     * @param CommandTester $command_tester
     */
    private function checkDbWriterOutput(CommandTester $command_tester)
    {
        $output = $command_tester->getDisplay();

        // Checking for people
        $this->assertContains('Persisted Person #2', $output);

        // Checking for tickets
        $this->assertContains('Creating new ticket with ref', $output);
        $this->assertContains('Persisted TicketLog #1', $output);
        $this->assertContains('Persisted TicketLog #2', $output);
        $this->assertContains('Persisted TicketMessage #1', $output);
        $this->assertContains('Persisted TicketMessage #2', $output);
        $this->assertContains('Persisted Ticket #1', $output);
        $this->assertContains('Persisted Ticket #2', $output);

        // Checking for news
        $this->assertContains('Persisted News #2', $output);
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
