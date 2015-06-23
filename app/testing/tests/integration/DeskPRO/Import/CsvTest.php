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
    private $output_path;

    /**
     * @var EntityRepository\Ticket
     */
    private $ticket_repository;

    /**
     * @var EntityRepository\Person
     */
    private $person_repository;

    /**
     * Set up
     */
    public function runBefore()
    {
        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $this->helper->enableFreshDatabaseSet('EmptyDb');

        $this->ticket_repository = $entity_manager->getRepository('Application\DeskPRO\Entity\Ticket');
        $this->person_repository = $entity_manager->getRepository('Application\DeskPRO\Entity\Person');

        $this->output_path = dp_get_data_dir() . '/import/csv/export';
        if ( ! is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);

        $this->checkDbEmpty();
        $this->checkJsonEmpty();
    }

    public function testCheck()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new CheckExportCommand());

        $command = $application->find('dp:export:check');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'csv',
            '--input-path' => DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/csv',
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $output = $commandTester->getDisplay();

        $this->assertContains('Attachment of entity `message_0` parsed successfully!', $output);
        $this->assertContains('Entity `message_1` parsed successfully!', $output);
        $this->assertContains('Entity `ticket_144` parsed successfully!', $output);
        $this->assertContains('Entity `person_0` parsed successfully!', $output);
        $this->assertContains('Entity `person_6` parsed successfully!', $output);
        $this->assertContains('Entity `article_0` parsed successfully!', $output);
        $this->assertContains('Entity `article_1` parsed successfully!', $output);
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
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'csv',
            '--input-path'  => DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/csv',
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
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'csv',
            '--input-path'  => DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/csv',
            '--batch'       => true,
        ));

        $this->checkDbData();
        $this->checkJsonEmpty();
    }

    public function testImportBatch()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ImportBatchCommand());

        $command = $application->find('dp:import:batch');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'csv',
            '--input-path'  => DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/csv',
            '--output-path' => $this->output_path,
            '--batch'       => true,
        ));

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
        $this->helper->seeFileFound('1/articles/article_0.json');
        $this->helper->seeInThisFile('Article 1');

        $this->helper->seeFileFound('1/feedback/feedback_1.json');
        $this->helper->seeInThisFile('Feedback 1');

        $this->helper->seeFileFound('1/people/person_2.json');
        $this->helper->seeInThisFile('Some Customer');

        $this->helper->seeFileFound('1/tickets/ticket_144.json');
        $this->helper->seeInThisFile('How to submit a ticket');
        $this->helper->seeInThisFile('Any update on my ticket yet?');
        $this->helper->seeInThisFile('Resources\/docs\/data_example\/csv\/tickets.csv');

        $this->helper->seeFileFound('1/news/news_0.json');
        $this->helper->seeInThisFile('News Title 1');

        $this->helper->seeFileFound('1/downloads/download_0.json');
        $this->helper->seeInThisFile('Download 1');
    }

    private function checkDbEmpty()
    {
        $this->assertEmpty($this->ticket_repository->findAll());
        $this->assertEmpty($this->person_repository->findAll());
    }

    private function checkDbData()
    {
        $this->assertCount(2, $this->ticket_repository->findAll());
        $this->assertCount(7, $this->person_repository->findAll());
    }
}
