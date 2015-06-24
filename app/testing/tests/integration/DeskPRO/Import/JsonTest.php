<?php

namespace DpIntegrationTests\DeskPRO\Import;

use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Command\CheckExportCommand;
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

        $this->output_path = dp_get_data_dir() . '/import/json/export';
        if ( ! is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);
    }

    public function testCheck()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new CheckExportCommand());

        $command = $application->find('dp:export:check');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'json',
            '--input-path' => DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/json',
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $output = $commandTester->getDisplay();

        $this->assertContains('Entity `message_1` parsed successfully!', $output);
        $this->assertContains('Entity `ticket_1` parsed successfully!', $output);
        $this->assertContains('Entity `person_710618382` parsed successfully!', $output);
        $this->assertContains('Done. Checking was successful.', $output);

        $this->checkDbEmpty();
        $this->checkJsonEmpty();
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

    private function checkDbEmpty()
    {
        $this->assertEmpty($this->ticket_repository->findAll());
        $this->assertEmpty($this->person_repository->findAll());
    }
}
