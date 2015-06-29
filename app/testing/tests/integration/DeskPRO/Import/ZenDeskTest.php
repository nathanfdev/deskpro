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

        $this->prepareReaderResponse();
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

        $output = $command_tester->getDisplay();

        $this->assertContains('Read 2 tickets', $output);
        $this->assertContains('[ZDTicket #1] Reading comments', $output);
        $this->assertContains('[ZDTicket #2] Reading comments', $output);
        $this->assertContains('Read 2 people', $output);
        $this->assertContains('Done. Checking was successful.', $output);
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

    private function prepareReaderResponse()
    {
        $date1 = new \DateTime('-1 year');
        $date2 = new \DateTime('-5 months');

        $this->adapter
            ->addTicketsIncrementalExportResponse((object)array(
                'tickets' => array(
                    (object)array(
                        'id'              => 1,
                        'submitter_id'    => 1,
                        'assignee_id'     => 3,
                        'subject'         => 'Ticket 1',
                        'description'     => 'Ticket description 1',
                        'status'          => 'new',
                        'priority'        => 'high',
                        'organization_id' => 1,
                        'created_at'      => $date1->format('Y-m-d H:i:s'),
                        'custom_fields'   => (object)array(),
                        'tags'            => (object)array('label 1', 'label 2'),
                    ),
                    (object)array(
                        'id'              => 2,
                        'submitter_id'    => 2,
                        'assignee_id'     => 4,
                        'subject'         => 'Ticket 2',
                        'description'     => 'Ticket description 2',
                        'status'          => 'open',
                        'priority'        => 'low',
                        'organization_id' => 1,
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'custom_fields'   => (object)array(),
                        'tags'            => (object)array('label 1', 'label 3'),
                    ),
                ),
            ))
            ->addTicketCommentsFindAllResponse((object)array(
                'comments' => array(),
            ))
            ->addTicketCommentsFindAllResponse((object)array(
                'comments' => array(),
            ))
            ->addPeopleFindResponse((object)array(
                'users' => array(
                    (object)array(
                        'id'              => 1,
                        'name'            => 'Person 1',
                        'email'           => 'person1@domain.tld',
                        'time_zone'       => 'Paris',
                        'role'            => 'end-user',
                        'created_at'      => $date1->format('Y-m-d H:i:s'),
                        'user_fields'     => array(),
                        'organization_id' => 1,
                    ),
                    (object)array(
                        'id'              => 2,
                        'name'            => 'Person 2',
                        'email'           => 'person2@domain.tld',
                        'time_zone'       => 'Moscow',
                        'role'            => 'agent',
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'user_fields'     => array(),
                        'organization_id' => 1,
                    ),
                ),
            ))
            ->addOrganizationFindResponse((object)array(
                'organization' => (object)array(
                    'id'   => 1,
                    'name' => 'An organization name',
                ),
            ))
        ;
    }
}
