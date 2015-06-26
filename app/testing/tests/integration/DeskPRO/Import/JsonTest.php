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
     * @var EntityRepository\Download
     */
    private $download_repository;

    /**
     * Set up
     */
    public function runBefore()
    {
        $this->helper->enableFreshDatabaseSet('EmptyDb');

        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $entity_manager->clear();

        $this->ticket_repository   = $entity_manager->getRepository('Application\DeskPRO\Entity\Ticket');
        $this->person_repository   = $entity_manager->getRepository('Application\DeskPRO\Entity\Person');
        $this->news_repository     = $entity_manager->getRepository('Application\DeskPRO\Entity\News');
        $this->article_repository  = $entity_manager->getRepository('Application\DeskPRO\Entity\Article');
        $this->feedback_repository = $entity_manager->getRepository('Application\DeskPRO\Entity\Feedback');
        $this->download_repository = $entity_manager->getRepository('Application\DeskPRO\Entity\Download');

        $this->input_path  = DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/json';
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
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'json',
            '--input-path'  => $this->input_path,
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
        $this->helper->seeFileFound('1/articles/article_1.json');
        $this->helper->seeFileFound('1/articles/article_2.json');
        $this->helper->seeFileFound('1/feedback/feedback_1.json');
        $this->helper->seeFileFound('1/people/person_710618382.json');
        $this->helper->seeFileFound('1/tickets/ticket_1.json');
        $this->helper->seeFileFound('1/news/news_1.json');
        $this->helper->seeFileFound('1/downloads/download_1.json');

        $this->checkJsonFile('/1/articles/article1.json', '1/articles/article_1.json');
        $this->checkJsonFile('/1/articles/article2.json', '1/articles/article_2.json');
        $this->checkJsonFile('/1/feedback/feedback1.json', '1/feedback/feedback_1.json');
        $this->checkJsonFile('/1/people/person1.json', '1/people/person_710618382.json');
        $this->checkJsonFile('/1/tickets/ticket1.json', '1/tickets/ticket_1.json');
        $this->checkJsonFile('/1/news/news1.json', '1/news/news_1.json');
        $this->checkJsonFile('/1/news/news2.json', '1/news/news_2.json');
        $this->checkJsonFile('/1/downloads/download1.json', '1/downloads/download_1.json');
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
        $this->assertEmpty($this->ticket_repository->findAll());
        $this->assertEmpty($this->person_repository->findAll());
        $this->assertEmpty($this->news_repository->findAll());
        $this->assertEmpty($this->article_repository->findAll());
        $this->assertEmpty($this->feedback_repository->findAll());
        $this->assertEmpty($this->download_repository->findAll());
    }

    private function checkDbData()
    {
        $this->assertCount(1, $this->ticket_repository->findAll());
        $this->assertCount(1, $this->person_repository->findAll());
    }
}
