<?php

namespace DpIntegrationTests\DeskPRO\Import;

use Application\DeskPRO\EntityRepository;
use Application\DeskPRO\Entity;
use Application\ImportBundle\Command\CheckExportCommand;
use Application\ImportBundle\Command\ExportCommand;
use Application\ImportBundle\Command\ImportBatchCommand;
use Application\ImportBundle\Command\ImportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class JsonTest
 * @package DpIntegrationTests\DeskPRO\Import
 *
 * @group importer
 */
class JsonTest extends \DpIntegrationTestCase
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
        $this->helper->loadFixtures('Import/Person');
        $this->helper->loadFixtures('Import/Organization');
        $this->helper->loadFixtures('Import/Ticket');

        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $entity_manager->clear();

        $this->ticket_repository              = $entity_manager->getRepository('Application\DeskPRO\Entity\Ticket');
        $this->ticket_attachment_repository   = $entity_manager->getRepository('Application\DeskPRO\Entity\TicketAttachment');
        $this->person_repository              = $entity_manager->getRepository('Application\DeskPRO\Entity\Person');
        $this->news_repository                = $entity_manager->getRepository('Application\DeskPRO\Entity\News');
        $this->article_repository             = $entity_manager->getRepository('Application\DeskPRO\Entity\Article');
        $this->feedback_repository            = $entity_manager->getRepository('Application\DeskPRO\Entity\Feedback');
        $this->feedback_attachment_repository = $entity_manager->getRepository('Application\DeskPRO\Entity\FeedbackAttachment');
        $this->download_repository            = $entity_manager->getRepository('Application\DeskPRO\Entity\Download');
        $this->organization_repository        = $entity_manager->getRepository('Application\DeskPRO\Entity\Organization');
        $this->blob_repository                = $entity_manager->getRepository('Application\DeskPRO\Entity\Blob');

        $this->input_path  = DP_ROOT . '/src/Application/ImportBundle/Resources/example/json';
        $this->output_path = dp_get_data_dir() . '/import/json/export';

        if ( ! is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->seeFileFound($this->input_path);
        $this->helper->seeFileFound($this->output_path);

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);

        if (file_exists($this->input_path . '/input.batch.json')) {
            $this->helper->deleteFile($this->input_path . '/input.batch.json');
        }

        $this->overrideDpRootPath('/1/downloads/download1.json');
        $this->overrideDpRootPath('/1/feedback/feedback1.json');
    }

    public function testCheck()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new CheckExportCommand());

        $command = $application->find('dp:export:check');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'json',
            '--input-path' => $this->input_path,
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $output = $command_tester->getDisplay();

        $this->assertContains('Entity `ticket_1` parsed successfully!', $output);
        $this->assertContains('Entity `person_710618382` parsed successfully!', $output);
        $this->assertContains('Entity `news_1` parsed successfully!', $output);
        $this->assertContains('Entity `feedback_1` parsed successfully!', $output);
        $this->assertContains('Entity `article_1` parsed successfully!', $output);
        $this->assertContains('Entity `article_2` parsed successfully!', $output);
        $this->assertContains('Entity `download_1` parsed successfully!', $output);
        $this->assertContains('Entity `organization_some_organization` parsed successfully!', $output);
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
            'script'        => 'json',
            '--input-path'  => $this->input_path,
            '--output-path' => $this->output_path,
            '--batch'       => true,
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
            'script'        => 'json',
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
            'script'        => 'json',
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
        $this->helper->seeFileFound('1/articles/article_2.json');
        $this->helper->seeFileFound('1/feedback/feedback_1.json');
        $this->helper->seeFileFound('1/people/person_710618382.json');
        $this->helper->seeFileFound('1/tickets/ticket_1.json');
        $this->helper->seeFileFound('1/news/news_1.json');
        $this->helper->seeFileFound('1/downloads/download_1.json');
        $this->helper->seeFileFound('1/organizations/organization_some_organization.json');

        $this->checkJsonFile('/1/articles/article1.json', '1/articles/article_1.json');
        $this->checkJsonFile('/1/articles/article2.json', '1/articles/article_2.json');
        $this->checkJsonFile('/1/feedback/feedback1.json', '1/feedback/feedback_1.json');
        $this->checkJsonFile('/1/people/person1.json', '1/people/person_710618382.json');
        $this->checkJsonFile('/1/tickets/ticket1.json', '1/tickets/ticket_1.json');
        $this->checkJsonFile('/1/news/news1.json', '1/news/news_1.json');
        $this->checkJsonFile('/1/news/news2.json', '1/news/news_2.json');
        $this->checkJsonFile('/1/downloads/download1.json', '1/downloads/download_1.json');
        $this->checkJsonFile('/1/organization_some_organization.json', '1/organization_some_organization.json');
    }

    private function checkJsonFile($input, $output)
    {
        $this->assertEquals(
            json_decode(file_get_contents($this->input_path . $input)),
            json_decode(file_get_contents($output))
        );
    }

    private function checkDbEmpty()
    {
        $this->assertEquals(1, $this->ticket_repository->countAll());
        $this->assertEquals(0, $this->ticket_attachment_repository->countAll());
        $this->assertEquals(2, $this->person_repository->countAll());
        $this->assertEquals(1, $this->news_repository->countAll());
        $this->assertEquals(1, $this->article_repository->countAll());
        $this->assertEquals(1, $this->feedback_repository->countAll());
        $this->assertEquals(0, $this->feedback_attachment_repository->countAll());
        $this->assertEquals(0, $this->download_repository->countAll());
        $this->assertEquals(1, $this->organization_repository->countAll());
        $this->assertEquals(0, $this->blob_repository->countAll());
    }

    private function checkDbData()
    {
        $this->checkDbArticleData();
        $this->checkDbPeopleData();
        $this->checkDbTicketsData();
        $this->checkDbFeedbackData();
        $this->checkDbOrganizationData();
        $this->checkDbBlobData();
    }

    private function checkDbArticleData()
    {
        $this->assertCount(2, $this->article_repository->findAll());

        /** @var Entity\Article $article */
        $article = $this->article_repository->findOneBy(array('id' => 2));
        $this->assertNotNull($article);

        $this->assertEquals('Article 1', $article->getRealTitle());
        $this->assertEquals('Content 1', $article->getContentPlain());
        $this->assertEquals('2-slug-article-1', $article->getUrlSlug());
        $this->assertEquals('published', $article->getStatusCode());
        $this->assertEquals(new \DateTime('2015-01-15 00:00:00'), $article->getDateCreated());
        $this->assertNull($article->getDatePublished());
        $this->assertNull($article->getDateEnd());

        $labels = array();
        foreach ($article->getLabels() as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('Label 1', 'Label 2', 'Label 3',), $labels);
        $this->assertCount(1, $article->getCustomData());

        /** @var Entity\CustomDataArticle $custom_data */
        $custom_data = $article->getCustomData()->first();
        $this->assertEquals(2, $custom_data->getArticleId());
        $this->assertEquals(1, $custom_data->getData());
    }

    private function checkDbPeopleData()
    {
        $this->assertCount(2, $this->person_repository->findAll());

        /** @var Entity\Person $person */
        $person = $this->person_repository->findOneBy(array('name' => 'Sergey'));
        $labels = array();
        foreach ($person->labels as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('label1', 'label2'), $labels);
    }

    private function checkDbTicketsData()
    {
        $this->assertCount(2, $this->ticket_repository->findAll());
        $this->assertCount(0, $this->ticket_attachment_repository->findAll());

        /** @var Entity\Ticket $ticket */
        $ticket = $this->ticket_repository->findOneBy(array('ref' => 'AAABBBCCC'));
        $this->assertNotNull($ticket);

        $labels = array();
        foreach ($ticket->labels as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('label1', 'label2'), $labels);
    }

    private function checkDbFeedbackData()
    {
        $this->assertCount(2, $this->feedback_repository->findAll());
        $this->assertCount(1, $this->feedback_attachment_repository->findAll());
    }

    private function checkDbOrganizationData()
    {
        $this->assertCount(1, $this->organization_repository->findAll());

        /** @var Entity\Organization $organization */
        $organization = $this->organization_repository->findOneBy(array('name' => 'Some Organization'));
        $this->assertNotNull($organization);

        $contact_data1 = $organization->getContactData('mobile');
        $contact = $contact_data1[0];
        $this->assertEquals('some comment', $contact->getComment());
        $this->assertEquals('country_calling_code', $contact->getField1());
        $this->assertEquals('number', $contact->getField2());
        $this->assertEquals('type', $contact->getField3());

        $this->assertEmpty($organization->getContactData('fax'));

        $labels = array();
        foreach ($organization->getLabels() as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('label1', 'label2'), $labels);
    }

    private function checkDbBlobData()
    {
        $this->assertCount(2, $this->blob_repository->findBy(array('content_type' => 'csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'downloads.csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'feedback.csv')));
    }

    private function checkDbWriterOutput(CommandTester $command_tester)
    {
        $output = $command_tester->getDisplay();

        // Checking for people
        $this->assertContains('Persisted Person #2', $output);

        // Checking for tickets
        $this->assertContains('Creating new ticket with ref', $output);
        $this->assertContains('Found existing ticket', $output);
        $this->assertContains('Persisted TicketLog #1', $output);
        $this->assertContains('Persisted TicketMessage #1', $output);
        $this->assertContains('Persisted TicketPriority #1', $output);
        $this->assertContains('Persisted Ticket #1', $output);
        $this->assertContains('Persisted Ticket #2', $output);

        // Checking for news
        $this->assertContains('Persisted News #2', $output);
        $this->assertContains('Unable to create `news` with oid `2`. Reason Person not found. Criteria: {"email":"some@email.tld"}', $output);

        // Checking for articles
        $this->assertContains('Persisted Article #2', $output);
        $this->assertContains('Unable to create `article` with oid `2`. Reason Person not found. Criteria: {"email":"another@email.tld"}', $output);

        // Checking for downloads
        $this->assertContains('Persisted Download #1', $output);
        $this->assertContains('Persisted DownloadCategory #2', $output);

        // Checking for feedback
        $this->assertContains('Persisted Feedback #2', $output);
    }

    private function overrideDpRootPath($file)
    {
        $dp_root = str_replace('/app', '/', DP_ROOT);
        $dp_root = str_replace('\'', '', $dp_root);
        $dp_root = str_replace('/', '\/', $dp_root);

        $content = file_get_contents($this->input_path . $file);
        $content = str_replace('\/deskpro\/www\/', $dp_root, $content);

        file_put_contents($this->input_path . $file, $content);
    }
}
