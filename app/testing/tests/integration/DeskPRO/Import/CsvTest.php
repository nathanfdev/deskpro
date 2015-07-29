<?php

namespace DpIntegrationTests\DeskPRO\Import;

use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Command\CheckExportCommand;
use Application\ImportBundle\Command\ExportCommand;
use Application\ImportBundle\Command\ImportBatchCommand;
use Application\ImportBundle\Command\ImportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ImportCsvTest
 * @package DpIntegrationTests\DeskPRO\Import
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

        $this->ticket_repository                   = $entity_manager->getRepository('Application\DeskPRO\Entity\Ticket');
        $this->ticket_attachment_repository        = $entity_manager->getRepository('Application\DeskPRO\Entity\TicketAttachment');
        $this->person_repository                   = $entity_manager->getRepository('Application\DeskPRO\Entity\Person');
        $this->news_repository                     = $entity_manager->getRepository('Application\DeskPRO\Entity\News');
        $this->article_repository                  = $entity_manager->getRepository('Application\DeskPRO\Entity\Article');
        $this->feedback_repository                 = $entity_manager->getRepository('Application\DeskPRO\Entity\Feedback');
        $this->feedback_attachment_repository      = $entity_manager->getRepository('Application\DeskPRO\Entity\FeedbackAttachment');
        $this->download_repository                 = $entity_manager->getRepository('Application\DeskPRO\Entity\Download');
        $this->organization_repository             = $entity_manager->getRepository('Application\DeskPRO\Entity\Organization');
        $this->blob_repository                     = $entity_manager->getRepository('Application\DeskPRO\Entity\Blob');
        $this->custom_data_ticket_repository       = $entity_manager->getRepository('Application\DeskPRO\Entity\CustomDataTicket');
        $this->custom_data_person_repository       = $entity_manager->getRepository('Application\DeskPRO\Entity\CustomDataPerson');
        $this->custom_data_feedback_repository     = $entity_manager->getRepository('Application\DeskPRO\Entity\CustomDataFeedback');
        $this->custom_data_article_repository      = $entity_manager->getRepository('Application\DeskPRO\Entity\CustomDataArticle');
        $this->custom_data_organization_repository = $entity_manager->getRepository('Application\DeskPRO\Entity\CustomDataOrganization');

        $this->input_path  = DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/csv';
        $this->output_path = dp_get_data_dir() . '/import/csv/export';

        if ( ! is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);

        $this->overrideDpRootPath('/ticket_attachments.csv');
        $this->overrideDpRootPath('/feedback_attachments.csv');
        $this->overrideDpRootPath('/downloads.csv');

        $this->checkDbEmpty();
        $this->checkJsonEmpty();
    }

    public function testCheck()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new CheckExportCommand());

        $command = $application->find('dp:export:check');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'csv',
            '--input-path' => $this->input_path,
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $output = $command_tester->getDisplay();

        $this->assertContains('Attachment of entity `message_0` parsed successfully!', $output);
        $this->assertContains('Entity `message_1` parsed successfully!', $output);
        $this->assertContains('Entity `ticket_144` parsed successfully!', $output);
        $this->assertContains('Entity `ticket_145` parsed successfully!', $output);
        $this->assertContains('Custom field of entity `ticket_144` parsed successfully!', $output);
        $this->assertContains('Entity `person_1` parsed successfully!', $output);
        $this->assertContains('Entity `person_6` parsed successfully!', $output);
        $this->assertContains('Entity `article_1` parsed successfully!', $output);
        $this->assertContains('Entity `article_2` parsed successfully!', $output);
        $this->assertContains('Entity `download_0` parsed successfully!', $output);
        $this->assertContains('Entity `feedback_1` parsed successfully!', $output);
        $this->assertContains('Entity `news_0` parsed successfully!', $output);
        $this->assertContains('Entity `news_1` parsed successfully!', $output);
        $this->assertContains('Done. Checking was successful.', $output);

        $this->checkDbEmpty();
        $this->checkJsonEmpty();
    }

    public function testExport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ExportCommand());

        $command = $application->find('dp:export:run');
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

        $command = $application->find('dp:import:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'csv',
            '--input-path'  => $this->input_path,
            '--verbose'     => true,
            '--batch'       => true,
        ));

        $this->checkDbWriterOutput($command_tester);
        $this->checkDbData();
        $this->checkJsonEmpty();
    }

    public function testImportBatch()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ImportBatchCommand());

        $command = $application->find('dp:import:batch');
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

        $this->helper->seeFileFound('1/feedback/feedback_1.json');
        $this->helper->seeInThisFile('Feedback 1');

        $this->helper->seeFileFound('1/people/person_3.json');
        $this->helper->seeInThisFile('Some Customer');

        $this->helper->seeFileFound('1/tickets/ticket_144.json');
        $this->helper->seeInThisFile('How to submit a ticket');
        $this->helper->seeInThisFile('Any update on my ticket yet?');
        $this->helper->seeInThisFile('Resources\/docs\/data_example\/csv\/tickets.csv');

        $this->helper->seeFileFound('1/tickets/ticket_145.json');
        $this->helper->seeInThisFile('Another Ticket');

        $this->helper->seeFileFound('1/news/news_0.json');
        $this->helper->seeInThisFile('News Title 1');

        $this->helper->seeFileFound('1/downloads/download_0.json');
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
        $this->assertEquals(1, $this->feedback_repository->countAll());
        $this->assertEquals(0, $this->feedback_attachment_repository->countAll());
        $this->assertEquals(0, $this->download_repository->countAll());
        $this->assertEquals(0, $this->blob_repository->countAll());
        $this->assertEquals(0, $this->custom_data_ticket_repository->countAll());
        $this->assertEquals(0, $this->custom_data_person_repository->countAll());
        $this->assertEquals(0, $this->custom_data_feedback_repository->countAll());
        $this->assertEquals(0, $this->custom_data_article_repository->countAll());
    }

    private function checkDbData()
    {
        $this->assertCount(3, $this->news_repository->findAll());

        // Checking for people
        $this->assertCount(8, $this->person_repository->findAll());
        $this->assertCount(2, $this->custom_data_person_repository->findAll());

        // Checking for tickets
        $this->assertNotEmpty($this->ticket_repository->findOneBy(array(
            'subject' => 'How to submit a ticket',
        )));
        $this->assertNotEmpty($this->ticket_repository->findOneBy(array(
            'subject' => 'Another Ticket',
        )));
        $this->assertCount(2, $this->ticket_repository->findAll());
        $this->assertCount(1, $this->ticket_attachment_repository->findAll());
        $this->assertCount(4, $this->custom_data_ticket_repository->findAll());

        // Checking for feedback
        $this->assertEquals(2, $this->feedback_repository->countAll());
        $this->assertEquals(1, $this->feedback_attachment_repository->countAll());
        $this->assertEquals(2, $this->custom_data_feedback_repository->countAll());

        // Checking for articles
        $this->assertEquals(3, $this->article_repository->countAll());
        $this->assertEquals(2, $this->custom_data_article_repository->countAll());

        // Checking for blob
        $this->assertCount(3, $this->blob_repository->findBy(array('content_type' => 'csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'downloads.csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'tickets.csv')));
    }
    
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

    private function overrideDpRootPath($file)
    {
        $dp_root = str_replace('/app', '/', DP_ROOT);

        $content = file_get_contents($this->input_path . $file);
        $content = str_replace('/deskpro/www/', $dp_root, $content);

        file_put_contents($this->input_path . $file, $content);
    }
}
