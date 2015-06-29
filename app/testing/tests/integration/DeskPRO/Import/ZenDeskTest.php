<?php

namespace DpIntegrationTests\DeskPRO\Import;

use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Command\CheckExportCommand;
use Application\ImportBundle\Reader\ZenDesk\Request\JsonMockAdapter;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderMockFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ZenDeskTest
 * @package DpIntegrationTests\DeskPRO\Import
 *
 * @group importer
 */
class ZenDeskTest extends \DpIntegrationTestCase
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
     * @var EntityRepository\TicketAttachment
     */
    private $ticket_attachment_repository;

    /**
     * @var EntityRepository\Person
     */
    private $person_repository;

    /**
     * @var JsonMockAdapter
     */
    private $adapter;

    /**
     * {@inheritdoc}
     */
    public function runBefore()
    {
        $this->helper->enableFreshDatabaseSet('FreshDb');

        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $entity_manager->clear();

        $this->ticket_repository            = $entity_manager->getRepository('Application\DeskPRO\Entity\Ticket');
        $this->ticket_attachment_repository = $entity_manager->getRepository('Application\DeskPRO\Entity\TicketAttachment');
        $this->person_repository            = $entity_manager->getRepository('Application\DeskPRO\Entity\Person');

        $this->output_path = dp_get_data_dir() . '/import/zendesk/export';
        if ( ! is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);

        $this->adapter = new JsonMockAdapter();

        $container = $this->helper->getSymfonyContainer()->getKernel()->getContainer();
        $container->set('deskpro.import.zendesk_reader_factory', new ZenDeskReaderMockFactory($this->adapter));

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
            'command'   => $command->getName(),
            'script'    => 'zendesk',
            '--verbose' => true,
            '--batch'   => true,
        ));
    }

    private function checkJsonEmpty()
    {
        $this->assertFalse(file_exists('1/people/'));
        $this->assertFalse(file_exists('1/tickets/'));
    }

    private function checkDbEmpty()
    {
        $this->assertEquals(0, $this->ticket_repository->countAll());
        $this->assertEquals(0, $this->ticket_attachment_repository->countAll());
        $this->assertEquals(1, $this->person_repository->countAll());
    }
}
