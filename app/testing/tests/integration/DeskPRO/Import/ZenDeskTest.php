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
use Application\ImportBundle\Generator\Exporter\Parser\ZenDesk\ArticleCategories;
use Application\ImportBundle\Reader\ZenDesk\Request\JsonMockAdapter;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderMockFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ZenDeskTest.
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
     * @var EntityRepository\CustomDefTicket
     */
    private $custom_def_ticket_repository;

    /**
     * @var EntityRepository\CustomDefPerson
     */
    private $custom_def_person_repository;

    /**
     * @var EntityRepository\CustomDefOrganization
     */
    private $custom_def_organization_repository;

    /**
     * @var JsonMockAdapter
     */
    private $adapter;

    /**
     * {@inheritdoc}
     */
    public function runBefore()
    {
        global $DP_CONFIG;

        $DP_CONFIG['zendesk_import'] = array(
            'subdomain'          => 'subdomain',
            'username'           => 'username',
            'password'           => 'password',
            'api_token'          => '',
            'initial_time'       => '2013-01-01 00:00:00',
            'connection_timeout' => 60,
        );

        $this->helper->enableFreshDatabaseSet('FreshDb');

        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $entity_manager->clear();

        $this->ticket_repository                  = $entity_manager->getRepository('DeskPRO:Ticket');
        $this->ticket_attachment_repository       = $entity_manager->getRepository('DeskPRO:TicketAttachment');
        $this->person_repository                  = $entity_manager->getRepository('DeskPRO:Person');
        $this->custom_def_ticket_repository       = $entity_manager->getRepository('DeskPRO:CustomDefTicket');
        $this->custom_def_person_repository       = $entity_manager->getRepository('DeskPRO:CustomDefPerson');
        $this->custom_def_organization_repository = $entity_manager->getRepository('DeskPRO:CustomDefOrganization');

        $this->output_path = dp_get_data_dir().'/import/zendesk/export';
        if (!is_dir($this->output_path)) {
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

        $command        = $application->find('dp:export:check');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'   => $command->getName(),
            'script'    => 'zendesk',
            '--verbose' => true,
            '--batch'   => true,
        ));

        $output = $command_tester->getDisplay();

        $this->assertContains('Read 4 tickets', $output);
        $this->assertNotContains('Skipping exception with ZDTicket: Unable to get submitter email', $output);
        $this->assertContains('[ZDTicket #1] Reading comments', $output);
        $this->assertContains('[ZDTicketComment #3] Skipping exception with ZDTicketComment: Comment without author_id, skipping', $output);
        $this->assertNotContains('[ZDTicketComment #4] Skipping exception with ZDTicketComment: Unable to get comment author, skipping', $output);
        $this->assertContains('[ZDAttachment #2] Skipping exception with ZDAttachment: Inline attachment, skipping', $output);
        $this->assertContains('[ZDAttachment #3] Skipping exception with ZDAttachment: Unable to download attachment', $output);

        $this->assertContains('[ZDTicket #2] Reading comments', $output);
        $this->assertContains('[ZDTicket #3] Reading comments', $output);
        $this->assertContains('Read 6 people', $output);
        $this->assertNotContains('[ZDPerson #3] Skipping exception with ZDPerson: Person without email, skipping', $output);
        $this->assertContains('Done. Checking was successful.', $output);

        $this->checkNoErrors($command_tester);
    }

    public function testExport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ExportCommand());

        $command        = $application->find('dp:export:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'zendesk',
            '--output-path' => $this->output_path,
            '--verbose'     => true,
            '--batch'       => true,
        ));

        $this->checkDbEmpty();
        $this->checkJsonData();
        $this->checkNoErrors($command_tester);
    }

    public function testImport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ImportCommand());

        $command        = $application->find('dp:import:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'zendesk',
            '--output-path' => $this->output_path,
            '--verbose'     => true,
            '--batch'       => true,
        ));

        $this->checkJsonEmpty();
        $this->checkNoErrors($command_tester);
        $this->checkDbWriterOutput($command_tester);
        $this->checkDbData();
    }

    public function testImportBatch()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ImportBatchCommand());

        $command        = $application->find('dp:import:batch');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'zendesk',
            '--output-path' => $this->output_path,
            '--verbose'     => true,
            '--batch'       => true,
        ));

        $this->checkDbWriterOutput($command_tester);
        $this->checkJsonData();
        $this->checkNoErrors($command_tester);

        // seems that exports in a wrong order, disable for a while
        $this->checkDbData();
    }

    private function checkJsonEmpty()
    {
        $this->assertFalse(file_exists('1/people/'));
        $this->assertFalse(file_exists('1/tickets/'));
    }

    private function checkJsonData()
    {
        $this->helper->seeFileFound('output.batch.json');

        // Checking for organization custom def
        $this->helper->seeFileFound('1/organizations_custom_def/organization_custom_def_1.json');
        $this->helper->seeFileFound('1/organizations_custom_def/organization_custom_def_2.json');
        $this->helper->seeFileFound('1/organizations_custom_def/organization_custom_def_3.json');
        $this->helper->seeFileFound('1/organizations_custom_def/organization_custom_def_4.json');
        $this->helper->seeFileFound('1/organizations_custom_def/organization_custom_def_5.json');
        $this->helper->seeFileFound('1/organizations_custom_def/organization_custom_def_6.json');

        // Checking for people custom def
        $this->helper->seeFileFound('1/people_custom_def/person_custom_def_1.json');
        $this->helper->seeFileFound('1/people_custom_def/person_custom_def_2.json');
        $this->helper->seeFileFound('1/people_custom_def/person_custom_def_3.json');
        $this->helper->seeFileFound('1/people_custom_def/person_custom_def_4.json');
        $this->helper->seeFileFound('1/people_custom_def/person_custom_def_5.json');
        $this->helper->seeFileFound('1/people_custom_def/person_custom_def_6.json');

        // Checking for people
        $this->helper->seeFileFound('1/people/person_1.json');
        $this->helper->seeInThisFile('Person 1');
        $this->helper->seeInThisFile('"is_disabled":false');
        $this->helper->seeInThisFile('"is_deleted":false');

        $this->helper->seeFileFound('1/people/person_2.json');
        $this->helper->seeInThisFile('Person 2');
        $this->helper->seeInThisFile('"is_disabled":false');
        $this->helper->seeInThisFile('"is_deleted":false');

        $this->helper->seeFileFound('1/people/person_100000.json');
        $this->helper->seeInThisFile('"oid":"100000"');
        $this->helper->seeInThisFile('"name":"Person 100000"');
        $this->helper->seeInThisFile('"is_disabled":true');
        $this->helper->seeInThisFile('"timezone":"America\/Los_Angeles"');
        $this->helper->seeInThisFile('"emails":["imported.user.100000@example.com"]');

        // Checking for ticket custom def
        $this->helper->seeFileFound('1/tickets_custom_def/ticket_custom_def_1.json');
        $this->helper->seeFileFound('1/tickets_custom_def/ticket_custom_def_2.json');
        $this->helper->seeFileFound('1/tickets_custom_def/ticket_custom_def_3.json');
        $this->helper->seeFileFound('1/tickets_custom_def/ticket_custom_def_4.json');
        $this->helper->seeFileFound('1/tickets_custom_def/ticket_custom_def_5.json');
        $this->helper->seeFileFound('1/tickets_custom_def/ticket_custom_def_6.json');

        // Checking for tickets
        $this->helper->seeFileFound('1/tickets/ticket_1.json');
        $this->helper->seeInThisFile('Ticket 1');
        $this->helper->seeInThisFile('"participants":["person1@domain.tld","imported.user.100000@example.com"]');

        $this->helper->seeFileFound('1/tickets/ticket_2.json');
        $this->helper->seeInThisFile('Ticket 2');
        $this->helper->seeInThisFile('"is_hold":false');
        $this->helper->seeInThisFile('"participants":[]');

        $this->helper->seeFileFound('1/tickets/ticket_3.json');
        $this->helper->seeInThisFile('Ticket 3');
        $this->helper->seeInThisFile('"person":"imported.user.3@example.com"');
        $this->helper->seeInThisFile('"agent":"imported.user.4@example.com"');

        $this->helper->seeFileFound('1/tickets/ticket_4.json');
        $this->helper->seeInThisFile('Ticket 4');
        $this->helper->seeInThisFile('"is_hold":true');
        $this->helper->seeInThisFile('"status":"awaiting_agent"');

        // Checking for article categories
        $this->helper->seeFileFound('1/article_categories/article_category_1.json');
        $this->helper->seeInThisFile('{"oid":1,"import_map_key":"zd_article_category","title":"Category 1","is_agent":false,"is_book":false,"user_groups":["everyone"],"categories":[{"oid":1,"import_map_key":null,"title":"Section 1","is_agent":false,"is_book":false,"user_groups":["registered"],"categories":[]}]}');

        // Checking for articles
        $this->helper->seeFileFound('1/articles/article_1.json');

        $this->helper->dontSeeInThisFile('Title (es_ES)');
        $this->helper->dontSeeInThisFile('Content (es_ES)');

        $this->helper->seeInThisFile('Title (de)');
        $this->helper->seeInThisFile('Content (de)');
        $this->helper->seeInThisFile('"categories":["Category 1 > Section 1"]');
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
        $date3 = new \DateTime('-2 months');
        $date4 = new \DateTime('-1 months');
        $now   = new \DateTime();

        $crm_fields = array(
            (object) array(
                'id'                    => 1,
                'type'                  => 'dropdown',
                'key'                   => 'drop_down_list_field',
                'title'                 => 'Drop-down list field',
                'raw_title'             => 'Drop-down list field',
                'description'           => 'Drop-down list field description',
                'raw_description'       => 'Drop-down list field description',
                'position'              => 0,
                'active'                => true,
                'system'                => false,
                'regexp_for_validation' => null,
                'created_at'            => $date1->format('c'),
                'updated_at'            => $date2->format('c'),
                'custom_field_options'  => array(
                    (object) array(
                        'id'       => 11,
                        'name'     => 'Option 1',
                        'raw_name' => 'Option 1',
                        'value'    => 'option_1',
                    ),
                    (object) array(
                        'id'       => 12,
                        'name'     => 'Option 2',
                        'raw_name' => 'Option 2',
                        'value'    => 'option_2',
                    ),
                    (object) array(
                        'id'       => 13,
                        'name'     => 'Option 3',
                        'raw_name' => 'Option 3',
                        'value'    => 'option_3',
                    ),
                    (object) array(
                        'id'       => 14,
                        'name'     => 'Option 3',
                        'raw_name' => 'Option 3',
                        'value'    => 'option_4_duplicate_title',
                    ),
                ),
            ),
            (object) array(
                'id'                    => 2,
                'type'                  => 'text',
                'key'                   => 'text_field',
                'title'                 => 'Text field',
                'raw_title'             => 'Text field',
                'description'           => 'Text field description',
                'raw_description'       => 'Text field description',
                'position'              => 1,
                'active'                => false,
                'system'                => false,
                'regexp_for_validation' => null,
                'created_at'            => $date1->format('c'),
                'updated_at'            => $date2->format('c'),
            ),
            (object) array(
                'id'                    => 3,
                'type'                  => 'integer',
                'key'                   => 'numeric',
                'title'                 => 'Numeric field',
                'raw_title'             => 'Numeric field',
                'description'           => 'Numeric field description',
                'raw_description'       => 'Numeric field description',
                'position'              => 2,
                'active'                => true,
                'system'                => false,
                'regexp_for_validation' => null,
                'created_at'            => $date1->format('c'),
                'updated_at'            => $date2->format('c'),
            ),
            (object) array(
                'id'                    => 4,
                'type'                  => 'decimal',
                'key'                   => 'decimal_field',
                'title'                 => 'Decimal field',
                'raw_title'             => 'Decimal field',
                'description'           => 'Decimal field description',
                'raw_description'       => 'Decimal field description',
                'position'              => 3,
                'active'                => true,
                'system'                => false,
                'regexp_for_validation' => null,
                'created_at'            => $date1->format('c'),
                'updated_at'            => $date2->format('c'),
            ),
            (object) array(
                'id'                    => 5,
                'type'                  => 'date',
                'key'                   => 'date_field',
                'title'                 => 'Date field',
                'raw_title'             => 'Date field',
                'description'           => 'Date field description',
                'raw_description'       => 'Date field description',
                'position'              => 4,
                'active'                => true,
                'system'                => false,
                'regexp_for_validation' => '\A([0-9]{4})-(1[0-2]|0[1-9])-(3[01]|[12][0-9]|0[1-9])\z',
                'created_at'            => $date1->format('c'),
                'updated_at'            => $date2->format('c'),
            ),
            (object) array(
                'id'                    => 6,
                'type'                  => 'regexp',
                'key'                   => 'regular_expression_field',
                'title'                 => 'Regular expression field',
                'raw_title'             => 'Regular expression field',
                'description'           => 'Regular expression field description',
                'raw_description'       => 'Regular expression field description',
                'position'              => 5,
                'active'                => true,
                'system'                => false,
                'regexp_for_validation' => '[0-9]+',
                'created_at'            => $date1->format('c'),
                'updated_at'            => $date2->format('c'),
            ),
        );

        $this->adapter
            ->addPeopleFieldsResponse((object) array(
                'user_fields' => $crm_fields,
            ))
            ->addTicketFieldsResponse((object) array(
                'ticket_fields' => array(
                    (object) array(
                        'id'                    => 1,
                        'type'                  => 'tickettype',
                        'title'                 => 'Type',
                        'raw_title'             => 'Type',
                        'description'           => 'Request type',
                        'raw_description'       => 'Request type',
                        'title_in_portal'       => 'Type',
                        'raw_title_in_portal'   => 'Type',
                        'tag'                   => null,
                        'regexp_for_validation' => null,
                        'position'              => 4,
                        'required'              => false,
                        'active'                => true,
                        'visible_in_portal'     => true,
                        'editable_in_portal'    => false,
                        'required_in_portal'    => false,
                        'system_field_options'  => array(
                            (object) array(
                                'name'  => 'Question',
                                'value' => 'question',
                            ),
                            (object) array(
                                'name'  => 'Incident',
                                'value' => 'incident',
                            ),
                            (object) array(
                                'name'  => 'Problem',
                                'value' => 'problem',
                            ),
                            (object) array(
                                'name'  => 'Task',
                                'value' => 'task',
                            ),
                        ),
                        'created_at' => $date1->format('c'),
                        'updated_at' => $date2->format('c'),
                        'removable'  => false,
                    ),
                    (object) array(
                        'id'                    => 2,
                        'type'                  => 'decimal',
                        'title'                 => 'Decimal field for agents',
                        'raw_title'             => 'Decimal field for agents',
                        'description'           => 'Decimal field for users description',
                        'raw_description'       => 'Decimal field for users description',
                        'title_in_portal'       => 'Decimal field for users',
                        'raw_title_in_portal'   => 'Decimal field for users',
                        'tag'                   => null,
                        'regexp_for_validation' => '\A[-+]?[0-9]*[.,]?[0-9]+\z',
                        'position'              => 9999,
                        'required'              => true,
                        'active'                => false,
                        'visible_in_portal'     => true,
                        'editable_in_portal'    => true,
                        'required_in_portal'    => false,
                        'created_at'            => $date1->format('c'),
                        'updated_at'            => $date2->format('c'),
                        'removable'             => true,
                    ),
                    (object) array(
                        'id'                    => 3,
                        'type'                  => 'integer',
                        'title'                 => 'Numeric field for agents',
                        'raw_title'             => 'Numeric field for agents',
                        'description'           => 'Numeric field for users description',
                        'raw_description'       => 'Numeric field for users description',
                        'title_in_portal'       => 'Numeric field for users',
                        'raw_title_in_portal'   => 'Numeric field for users',
                        'tag'                   => null,
                        'regexp_for_validation' => '\A[-+]?\d+\z',
                        'position'              => 9999,
                        'required'              => true,
                        'active'                => true,
                        'visible_in_portal'     => true,
                        'editable_in_portal'    => true,
                        'required_in_portal'    => true,
                        'created_at'            => $date1->format('c'),
                        'updated_at'            => $date2->format('c'),
                        'removable'             => true,
                    ),
                    (object) array(
                        'id'                    => 4,
                        'type'                  => 'checkbox',
                        'title'                 => 'Checkbox field for agents',
                        'raw_title'             => 'Checkbox field for agents',
                        'description'           => 'Checkbox field for users Description',
                        'raw_description'       => 'Checkbox field for users Description',
                        'title_in_portal'       => 'Checkbox field for users',
                        'raw_title_in_portal'   => 'Checkbox field for users',
                        'position'              => 9999,
                        'required'              => true,
                        'active'                => true,
                        'visible_in_portal'     => true,
                        'editable_in_portal'    => true,
                        'required_in_portal'    => true,
                        'regexp_for_validation' => null,
                        'tag'                   => 'my_checkbox_tag',
                        'created_at'            => $date1->format('c'),
                        'updated_at'            => $date2->format('c'),
                        'removable'             => true,
                    ),
                    (object) array(
                        'id'                    => 5,
                        'type'                  => 'tagger',
                        'title'                 => 'My drop down list',
                        'raw_title'             => 'My drop down list',
                        'description'           => '',
                        'raw_description'       => '',
                        'title_in_portal'       => 'My drop down list (for users)',
                        'raw_title_in_portal'   => 'My drop down list (for users)',
                        'regexp_for_validation' => null,
                        'position'              => 9999,
                        'required'              => true,
                        'active'                => true,
                        'visible_in_portal'     => true,
                        'editable_in_portal'    => false,
                        'required_in_portal'    => false,
                        'tag'                   => null,
                        'created_at'            => $date1->format('c'),
                        'updated_at'            => $date2->format('c'),
                        'removable'             => true,
                        'custom_field_options'  => array(
                            (object) array(
                                'id'       => 51,
                                'name'     => 'Option 1',
                                'raw_name' => 'Option 1',
                                'value'    => 'option_1',
                            ),
                            (object) array(
                                'id'       => 52,
                                'name'     => 'Option 2',
                                'raw_name' => 'Option 2',
                                'value'    => 'option_2',
                            ),
                            (object) array(
                                'id'       => 53,
                                'name'     => 'Option 3',
                                'raw_name' => 'Option 3',
                                'value'    => 'option_3',
                            ),
                            (object) array(
                                'id'       => 54,
                                'name'     => 'Option 3',
                                'raw_name' => 'Option 3',
                                'value'    => 'option_4_duplicate_title',
                            ),
                        ),
                    ),
                    (object) array(
                        'id'                    => 6,
                        'type'                  => 'regexp',
                        'title'                 => 'Regular expression field for agents',
                        'raw_title'             => 'Regular expression field for agents',
                        'description'           => 'Regular expression field for agents Description',
                        'raw_description'       => 'Regular expression field for agents Description',
                        'title_in_portal'       => 'Regular expression field for agents',
                        'raw_title_in_portal'   => 'Regular expression field for agents',
                        'regexp_for_validation' => '\d+',
                        'position'              => 9999,
                        'required'              => true,
                        'active'                => true,
                        'visible_in_portal'     => true,
                        'editable_in_portal'    => false,
                        'required_in_portal'    => false,
                        'tag'                   => null,
                        'created_at'            => $date1->format('c'),
                        'updated_at'            => $date2->format('c'),
                        'removable'             => true,
                    ),
                ),
            ))
            ->addOrganizationFieldsResponse((object) array(
                'organization_fields' => $crm_fields,
            ))
            ->addOrganizationFindAllResponse((object) array(
                'organizations' => array(),
            ))
            ->addTicketsIncrementalExportResponse((object) array(
                'tickets' => array(
                    (object) array(
                        'id'               => 1,
                        'requester_id'     => 1,
                        'assignee_id'      => 3,
                        'subject'          => 'Ticket 1',
                        'description'      => 'Ticket description 1',
                        'status'           => 'closed',
                        'priority'         => 'high',
                        'organization_id'  => 1,
                        'created_at'       => $date1->format('Y-m-d H:i:s'),
                        'custom_fields'    => (object) array(),
                        'tags'             => (object) array('label 1', 'label 2'),
                        'collaborator_ids' => (object) array(1, 100000),
                    ),
                    (object) array(
                        'id'              => 2,
                        'requester_id'    => 2,
                        'assignee_id'     => 4,
                        'subject'         => 'Ticket 2',
                        'description'     => 'Ticket description 2',
                        'status'          => 'open',
                        'priority'        => 'low',
                        'organization_id' => 1,
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'custom_fields'   => (object) array(),
                        'tags'            => (object) array('label 1', 'label 3'),
                    ),
                    (object) array(
                        'id'              => 3,
                        'requester_id'    => 3,
                        'assignee_id'     => 4,
                        'subject'         => 'Ticket 3',
                        'description'     => 'Ticket description 3',
                        'status'          => 'open',
                        'priority'        => 'low',
                        'organization_id' => 1,
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'custom_fields'   => (object) array(),
                        'tags'            => (object) array('label 1', 'label 3'),
                    ),
                    (object) array(
                        'id'              => 4,
                        'requester_id'    => 1,
                        'assignee_id'     => 4,
                        'subject'         => 'Ticket 4',
                        'description'     => 'Ticket description 4',
                        'status'          => 'hold',
                        'priority'        => 'low',
                        'organization_id' => 1,
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'custom_fields'   => (object) array(),
                        'tags'            => (object) array('label 2', 'label 3'),
                    ),
                ),
                'end_time' => $now->getTimestamp(),
            ))
            ->addTicketCommentsFindAllResponse((object) array(
                'comments' => array(
                    (object) array(
                        'id'          => 1,
                        'author_id'   => 1,
                        'body'        => 'Reply #1',
                        'public'      => true,
                        'created_at'  => $date3->format('Y-m-d H:i:s'),
                        'attachments' => array(
                            (object) array(
                                'id'           => 1,
                                'file_name'    => 'file 1',
                                'content_type' => 'image/png',
                                'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                            ),
                            (object) array(
                                'id'           => 2,
                                'file_name'    => 'file 1',
                                'content_type' => 'image/png',
                                'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                                'inline'       => true,
                            ),
                            (object) array(
                                'id'           => 3,
                                'file_name'    => 'file 3',
                                'content_type' => 'image/png',
                                'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/nologo.png',
                            ),
                        ),
                    ),
                    (object) array(
                        'id'          => 2,
                        'author_id'   => 2,
                        'body'        => 'Reply #2',
                        'public'      => true,
                        'created_at'  => $date4->format('Y-m-d H:i:s'),
                        'attachments' => array(),
                    ),
                    (object) array(
                        'id'          => 3,
                        'author_id'   => null,
                        'body'        => 'Reply #3',
                        'public'      => true,
                        'created_at'  => $date4->format('Y-m-d H:i:s'),
                        'attachments' => array(),
                    ),
                    (object) array(
                        'id'          => 4,
                        'author_id'   => 3,
                        'body'        => 'Reply #4',
                        'public'      => true,
                        'created_at'  => $date4->format('Y-m-d H:i:s'),
                        'attachments' => array(),
                    ),
                ),
            ))
            ->addTicketCommentsFindAllResponse((object) array(
                'comments' => array(
                    (object) array(
                        'id'         => 1,
                        'body'       => 'Comment 1',
                        'author_id'  => 1,
                        'created_at' => $date2->format('Y-m-d H:i:s'),
                    ),
                    (object) array(
                        'id'         => 2,
                        'body'       => 'Comment 1',
                        'author_id'  => 3,
                        'created_at' => $date4->format('Y-m-d H:i:s'),
                    ),
                ),
            ))
            ->addTicketCommentsFindAllResponse((object) array(
                'comments' => array(),
            ))
            ->addTicketCommentsFindAllResponse((object) array(
                'comments' => array(),
            ))
            ->addArticleCategoriesFindAll((object) array(
                'categories' => array(
                    (object) array(
                        'id'              => 1,
                        'name'            => 'Category 1',
                        'description'     => 'Category description',
                        'locale'          => 'en-gb',
                        'source_locale'   => 'ru',
                        'url'             => 'http://url.com/',
                        'html_url'        => 'http://url.com/',
                        'category_id'     => 1,
                        'outdated'        => false,
                        'position'        => 0,
                        'translation_ids' => array(),
                        'created_at'      => $date1->format('Y-m-d H:i:s'),
                        'updated_at'      => $date2->format('Y-m-d H:i:s'),
                    ),
                ),
            ))
            ->addArticleSectionsFindAll((object) array(
                'sections' => array(
                    (object) array(
                        'id'              => 1,
                        'name'            => 'Section 1',
                        'description'     => 'Section description',
                        'locale'          => 'en-gb',
                        'source_locale'   => 'ru',
                        'url'             => 'http://url.com/',
                        'html_url'        => 'http://url.com/',
                        'category_id'     => 1,
                        'outdated'        => false,
                        'position'        => 0,
                        'translation_ids' => array(),
                        'created_at'      => $date1->format('Y-m-d H:i:s'),
                        'updated_at'      => $date2->format('Y-m-d H:i:s'),
                    ),
                ),
            ))
            ->addArticleSectionAccessPolicyFindResponse((object) array(
                'access_policy' => (object) array(
                    'viewable_by'                    => ArticleCategories::VIEWABLE_BY_SIGNED,
                    'manageable_by'                  => ArticleCategories::VIEWABLE_BY_STAFF,
                    'restricted_to_group_ids'        => array(),
                    'restricted_to_organization_ids' => array(),
                    'required_tags'                  => array(),
                ),
            ))
            ->addArticlesIncrementalExportResponse((object) array(
                'articles' => array(
                    (object) array(
                        'id'          => 1,
                        'author_id'   => 1,
                        'section_id'  => 1,
                        'title'       => 'Article 1',
                        'body'        => 'Article content',
                        'created_at'  => $date1->format('Y-m-d H:i:s'),
                        'updated_at'  => $date2->format('Y-m-d H:i:s'),
                        'vote_sum'    => 10,
                        'vote_count'  => 5,
                        'locale'      => 'en-us',
                        'draft'       => false,
                        'label_names' => array('Label 1', 'Label 2'),
                    ),
                    (object) array(
                        'id'          => 2,
                        'author_id'   => 1,
                        'section_id'  => 1,
                        'title'       => 'Article 2',
                        'body'        => 'Article content',
                        'created_at'  => $date2->format('Y-m-d H:i:s'),
                        'updated_at'  => $date3->format('Y-m-d H:i:s'),
                        'vote_sum'    => 10,
                        'vote_count'  => 5,
                        'locale'      => 'en-us',
                        'draft'       => true,
                        'label_names' => array('Label 1', 'Label 3'),
                    ),
                    (object) array(
                        'id'          => 3,
                        'author_id'   => 200000,
                        'section_id'  => 1,
                        'title'       => 'Article 3 (with fake user)',
                        'body'        => 'Article content',
                        'created_at'  => $date2->format('Y-m-d H:i:s'),
                        'updated_at'  => $date3->format('Y-m-d H:i:s'),
                        'vote_sum'    => 10,
                        'vote_count'  => 5,
                        'locale'      => 'en-us',
                        'draft'       => true,
                        'label_names' => array('Label 5', 'Label 6'),
                    ),
                ),
                'end_time' => $now->getTimestamp(),
            ))
            ->addArticleCommentsFindAllResponse((object) array(
                'comments' => array(
                    (object) array(
                        'id'         => 1,
                        'body'       => 'Comment 1',
                        'author_id'  => 2,
                        'created_at' => $date3->format('Y-m-d H:i:s'),
                    ),
                    (object) array(
                        'id'         => 2,
                        'body'       => 'Comment 2',
                        'author_id'  => 3,
                        'created_at' => $date4->format('Y-m-d H:i:s'),
                    ),
                ),
            ))
            ->addArticleCommentsFindAllResponse((object) array(
                'comments' => array(
                    (object) array(
                        'id'         => 3,
                        'body'       => 'Comment 3',
                        'author_id'  => 2,
                        'created_at' => $date3->format('Y-m-d H:i:s'),
                    ),
                    (object) array(
                        'id'         => 4,
                        'body'       => 'Comment 4',
                        'author_id'  => 3,
                        'created_at' => $date4->format('Y-m-d H:i:s'),
                    ),
                ),
            ))
            ->addArticleCommentsFindAllResponse((object) array(
                'comments' => array(),
            ))
            ->addArticleAttachmentsFindAllResponse((object) array(
                'article_attachments' => array(
                    (object) array(
                        'id'           => 1,
                        'file_name'    => 'file 1',
                        'content_type' => 'image/png',
                        'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                    ),
                ),
            ))
            ->addArticleAttachmentsFindAllResponse((object) array(
                'article_attachments' => array(
                    (object) array(
                        'id'           => 1,
                        'file_name'    => 'file 1',
                        'content_type' => 'image/png',
                        'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                    ),
                ),
            ))
            ->addArticleAttachmentsFindAllResponse((object) array(
                'article_attachments' => array(),
            ))
            ->addArticleTranslationsFindAllResponse((object) array(
                'translations' => array(
                    (object) array(
                        'id'     => '1',
                        'locale' => 'es',
                        'title'  => 'Title (es_ES)',
                        'body'   => 'Content (es_ES)',
                        'draft'  => true,
                    ),
                    (object) array(
                        'id'     => '2',
                        'locale' => 'de',
                        'title'  => 'Title (de)',
                        'body'   => 'Content (de)',
                        'draft'  => false,
                    ),
                ),
            ))
            ->addArticleTranslationsFindAllResponse((object) array(
                'translations' => array(),
            ))
            ->addArticleTranslationsFindAllResponse((object) array(
                'translations' => array(),
            ))
            ->addPeopleFindResponse((object) array(
                'users' => array(
                    (object) array(
                        'id'              => 1,
                        'name'            => 'Person 1',
                        'email'           => 'person1@domain.tld',
                        'time_zone'       => 'Paris',
                        'role'            => 'end-user',
                        'created_at'      => $date1->format('Y-m-d H:i:s'),
                        'user_fields'     => array(),
                        'organization_id' => 1,
                        'tags'            => array('Tag 1', 'Tag 2'),
                    ),
                    (object) array(
                        'id'              => 2,
                        'name'            => 'Person 2',
                        'email'           => 'person2@domain.tld',
                        'time_zone'       => 'Moscow',
                        'role'            => 'agent',
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'user_fields'     => array(),
                        'organization_id' => 1,
                        'tags'            => array('Tag 2', 'Tag 3'),
                    ),
                    (object) array(
                        'id'              => 3,
                        'name'            => 'Person 3',
                        'email'           => null,
                        'time_zone'       => 'Moscow',
                        'role'            => 'end-user',
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'user_fields'     => array(),
                        'organization_id' => 1,
                    ),
                ),
            ))
            ->addPeopleFindResponse((object) array(
                'user' => array(
                    'id'              => 100000,
                    'name'            => 'Person 100000',
                    'email'           => null,
                    'time_zone'       => 'America/Los_Angeles',
                    'role'            => 'end-user',
                    'created_at'      => $date1->format('Y-m-d H:i:s'),
                    'user_fields'     => array(),
                    'organization_id' => 1,
                    'tags'            => array(),
                ),
            ))
            ->addPeopleFindResponse((object) array(
                'user' => array(
                    'id'              => 4,
                    'name'            => 'Person 4',
                    'email'           => null,
                    'time_zone'       => 'Paris',
                    'role'            => 'end-user',
                    'created_at'      => $date1->format('Y-m-d H:i:s'),
                    'user_fields'     => array(),
                    'organization_id' => 1,
                    'tags'            => array(),
                ),
            ))
            ->addPeopleFindResponse((object) array(
                'users' => array(),
            ))
            ->addPeopleFindResponse((object) array(
            ))
            ->addOrganizationFindResponse((object) array(
                'organization' => (object) array(
                    'id'   => 1,
                    'name' => 'An organization name',
                ),
            ));
    }

    /**
     * @param CommandTester $command_tester
     */
    private function checkDbWriterOutput(CommandTester $command_tester)
    {
        $output = $command_tester->getDisplay();

        $this->assertContains('Unable to set ticket agent, `imported.user.4@example.com` is not an agent', $output);
        $this->assertContains('Creating new person with email `imported.user.100000@example.com`', $output);
        $this->assertContains('Creating new person with email `imported.user.100000@example.com`', $output);
    }

    /**
     * @param CommandTester $command_tester
     */
    private function checkNoErrors(CommandTester $command_tester)
    {
        $output = $command_tester->getDisplay();

        $this->assertNotContains('ERROR', $output);
        $this->assertNotContains('CRITICAL', $output);
    }

    private function checkDbData()
    {
        $this->checkDbTicketsData();
        $this->checkDbPeopleData();
        $this->checkDbPeopleCustomDefData();
        $this->checkDbTicketsCustomDefData();
        $this->checkDbOrganizationsCustomDefData();
    }

    private function checkDbTicketsData()
    {
        /** @var Entity\Ticket[] $tickets */
        $tickets = $this->ticket_repository->findAll();
        $this->assertCount(4, $tickets);

        $ticket = $tickets[0];

        $this->assertNotNull($ticket);
        $this->assertEquals('Ticket 1', $ticket->getTitle());
        $this->assertEquals('archived', $ticket->getStatusCode());
        $this->assertFalse($ticket->isHold());

        $this->assertNotNull($ticket->hasParticipantEmailAddress('person1@domain.tld'));

        $ticket = $tickets[1];

        $this->assertNotNull($ticket);
        $this->assertEquals('Ticket 2', $ticket->getTitle());
        $this->assertEquals('awaiting_agent', $ticket->getStatusCode());
        $this->assertFalse($ticket->isHold());

        $ticket = $tickets[2];

        $this->assertNotNull($ticket);
        $this->assertEquals('Ticket 3', $ticket->getTitle());
        $this->assertEquals('awaiting_agent', $ticket->getStatusCode());
        $this->assertFalse($ticket->isHold());

        $ticket = $tickets[3];

        $this->assertNotNull($ticket);
        $this->assertEquals('Ticket 4', $ticket->getTitle());
        $this->assertEquals('awaiting_agent', $ticket->getStatusCode());
        $this->assertTrue($ticket->isHold());
    }

    private function checkDbPeopleData()
    {
        $person = $this->person_repository->findOneByEmail('person1@domain.tld');
        $labels = array();
        foreach ($person->labels as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('Tag 1', 'Tag 2'), $labels);
        $this->assertFalse($person->isDisabled());
        $this->assertFalse($person->isDeleted());

        $person = $this->person_repository->findOneByEmail('person2@domain.tld');
        $labels = array();
        foreach ($person->labels as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('Tag 2', 'Tag 3'), $labels);
        $this->assertFalse($person->isDisabled());
        $this->assertFalse($person->isDeleted());

        $person = $this->person_repository->findOneByEmail('imported.user.4@example.com');
        $this->assertTrue($person->isDisabled());
        $this->assertFalse($person->isDeleted());

        $person = $this->person_repository->findOneByEmail('imported.user.100000@example.com');
        $this->assertTrue($person->isDisabled());
        $this->assertFalse($person->isDeleted());
        $this->assertEquals('America/Los_Angeles', $person->getTimezone());

        $person = $this->person_repository->findOneByEmail('imported.user.200000@example.com');
        $this->assertTrue($person->isDisabled());
        $this->assertFalse($person->isDeleted());
    }

    private function checkDbPeopleCustomDefData()
    {
        $this->assertEquals(10, $this->custom_def_person_repository->countAll());

        /** @var Entity\CustomDefPerson $custom_def */
        $custom_def = $this->custom_def_person_repository->find(1);
        $this->assertEquals('Drop-down list field', $custom_def->getTitle());
        $this->assertEquals('Drop-down list field description', $custom_def->getDescription());
        $this->assertEquals(Entity\CustomDefAbstract::HANDLER_CLASS_CHOICE, $custom_def->getHandlerClass());
        $this->assertTrue($custom_def->isEnabled());

        $this->assertCount(4, $custom_def->getAllChildren());

        $custom_def = $this->custom_def_person_repository->find(6);
        $this->assertEquals('Text field', $custom_def->getTitle());
        $this->assertEquals('Text field description', $custom_def->getDescription());
        $this->assertEquals(Entity\CustomDefAbstract::HANDLER_CLASS_TEXT, $custom_def->getHandlerClass());
        $this->assertFalse($custom_def->isEnabled());

        $custom_def = $this->custom_def_person_repository->find(7);
        $this->assertEquals('Numeric field', $custom_def->getTitle());
        $this->assertEquals('Numeric field description', $custom_def->getDescription());
        $this->assertEquals(Entity\CustomDefAbstract::HANDLER_CLASS_TEXT, $custom_def->getHandlerClass());
        $this->assertEquals('regex', $custom_def->getOption('validation_type'));
        $this->assertEquals('regex', $custom_def->getOption('agent_validation_type'));
        $this->assertEquals('/^[-+]?\d+$/', $custom_def->getOption('regex'));
        $this->assertEquals('/^[-+]?\d+$/', $custom_def->getOption('agent_regex'));
        $this->assertTrue($custom_def->isEnabled());

        $custom_def = $this->custom_def_person_repository->find(8);
        $this->assertEquals('Decimal field', $custom_def->getTitle());
        $this->assertEquals('Decimal field description', $custom_def->getDescription());
        $this->assertEquals(Entity\CustomDefAbstract::HANDLER_CLASS_TEXT, $custom_def->getHandlerClass());
        $this->assertEquals('regex', $custom_def->getOption('validation_type'));
        $this->assertEquals('regex', $custom_def->getOption('agent_validation_type'));
        $this->assertEquals('/^[-+]?[0-9]*[.,]?[0-9]+$/', $custom_def->getOption('regex'));
        $this->assertEquals('/^[-+]?[0-9]*[.,]?[0-9]+$/', $custom_def->getOption('agent_regex'));
        $this->assertTrue($custom_def->isEnabled());

        $custom_def = $this->custom_def_person_repository->find(9);
        $this->assertEquals('Date field', $custom_def->getTitle());
        $this->assertEquals('Date field description', $custom_def->getDescription());
        $this->assertEquals(Entity\CustomDefAbstract::HANDLER_CLASS_DATE, $custom_def->getHandlerClass());
        $this->assertTrue($custom_def->isEnabled());

        $custom_def = $this->custom_def_person_repository->find(10);
        $this->assertEquals('Regular expression field', $custom_def->getTitle());
        $this->assertEquals('Regular expression field description', $custom_def->getDescription());
        $this->assertEquals(Entity\CustomDefAbstract::HANDLER_CLASS_TEXT, $custom_def->getHandlerClass());
        $this->assertEquals('regex', $custom_def->getOption('validation_type'));
        $this->assertEquals('regex', $custom_def->getOption('agent_validation_type'));
        $this->assertEquals('/[0-9]+/', $custom_def->getOption('regex'));
        $this->assertEquals('/[0-9]+/', $custom_def->getOption('agent_regex'));
        $this->assertTrue($custom_def->isEnabled());
    }

    private function checkDbTicketsCustomDefData()
    {
        $this->assertEquals(14, $this->custom_def_ticket_repository->countAll());
    }

    private function checkDbOrganizationsCustomDefData()
    {
        $this->assertEquals(10, $this->custom_def_organization_repository->countAll());
    }
}
