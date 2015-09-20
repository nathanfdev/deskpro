<?php

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

        $this->ticket_repository            = $entity_manager->getRepository('DeskPRO:Ticket');
        $this->ticket_attachment_repository = $entity_manager->getRepository('DeskPRO:TicketAttachment');
        $this->person_repository            = $entity_manager->getRepository('DeskPRO:Person');

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

        $this->assertContains('Read 4 tickets', $output);
        $this->assertContains('[ZDTicket #3] Skipping exception with ZDTicket: Unable to get submitter email by id #3', $output);
        $this->assertContains('[ZDTicket #1] Reading comments', $output);
        $this->assertContains('[ZDTicketComment #3] Skipping exception with ZDTicketComment: Comment without author_id, skipping', $output);
        $this->assertContains('[ZDTicketComment #4] Skipping exception with ZDTicketComment: Unable to get comment author, skipping', $output);
        $this->assertContains('[ZDAttachment #2] Skipping exception with ZDAttachment: Inline attachment, skipping', $output);
        $this->assertContains('[ZDAttachment #3] Skipping exception with ZDAttachment: Unable to download attachment', $output);

        $this->assertContains('[ZDTicket #2] Reading comments', $output);
        $this->assertContains('[ZDTicket #3] Reading comments', $output);
        $this->assertContains('Read 6 people', $output);
        $this->assertContains('[ZDPerson #3] Skipping exception with ZDPerson: Person without email, skipping', $output);
        $this->assertContains('Done. Checking was successful.', $output);

        $this->checkNoErrors($command_tester);
    }

    public function testExport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ExportCommand());

        $command = $application->find('dp:export:run');
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

        $command = $application->find('dp:import:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'   => $command->getName(),
            'script'    => 'zendesk',
            '--output-path' => $this->output_path,
            '--verbose' => true,
            '--batch'   => true,
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

        $command = $application->find('dp:import:batch');
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
        $this->helper->seeInThisFile('"is_disabled":true');
        $this->helper->seeInThisFile('"timezone":"Etc\/UTC"');
        $this->helper->seeInThisFile('"emails":["imported.user.100000@example.com"]');

        // Checking for tickets
        $this->helper->seeFileFound('1/tickets/ticket_1.json');
        $this->helper->seeInThisFile('Ticket 1');
        $this->helper->seeInThisFile('"participants":["person1@domain.tld","imported.user.100000@example.com"]');

        $this->helper->seeFileFound('1/tickets/ticket_2.json');
        $this->helper->seeInThisFile('Ticket 2');
        $this->helper->seeInThisFile('"is_hold":false');
        $this->helper->seeInThisFile('"participants":[]');

        $this->assertFalse(file_exists('1/tickets/ticket_3.json'));

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

        $this->adapter
            ->addTicketsIncrementalExportResponse((object)array(
                'tickets'  => array(
                    (object)array(
                        'id'               => 1,
                        'requester_id'     => 1,
                        'assignee_id'      => 3,
                        'subject'          => 'Ticket 1',
                        'description'      => 'Ticket description 1',
                        'status'           => 'closed',
                        'priority'         => 'high',
                        'organization_id'  => 1,
                        'created_at'       => $date1->format('Y-m-d H:i:s'),
                        'custom_fields'    => (object)array(),
                        'tags'             => (object)array('label 1', 'label 2'),
                        'collaborator_ids' => (object)array(1, 100000),
                    ),
                    (object)array(
                        'id'              => 2,
                        'requester_id'    => 2,
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
                    (object)array(
                        'id'              => 3,
                        'requester_id'    => 3,
                        'assignee_id'     => 4,
                        'subject'         => 'Ticket 3',
                        'description'     => 'Ticket description 3',
                        'status'          => 'open',
                        'priority'        => 'low',
                        'organization_id' => 1,
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'custom_fields'   => (object)array(),
                        'tags'            => (object)array('label 1', 'label 3'),
                    ),
                    (object)array(
                        'id'              => 4,
                        'requester_id'    => 1,
                        'assignee_id'     => 4,
                        'subject'         => 'Ticket 4',
                        'description'     => 'Ticket description 4',
                        'status'          => 'hold',
                        'priority'        => 'low',
                        'organization_id' => 1,
                        'created_at'      => $date2->format('Y-m-d H:i:s'),
                        'custom_fields'   => (object)array(),
                        'tags'            => (object)array('label 2', 'label 3'),
                    ),
                ),
                'end_time' => $now->getTimestamp(),
            ))
            ->addTicketCommentsFindAllResponse((object)array(
                'comments' => array(
                    (object)array(
                        'id'          => 1,
                        'author_id'   => 1,
                        'body'        => 'Reply #1',
                        'public'      => true,
                        'created_at'  => $date3->format('Y-m-d H:i:s'),
                        'attachments' => array(
                            (object)array(
                                'id'           => 1,
                                'file_name'    => 'file 1',
                                'content_type' => 'image/png',
                                'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                            ),
                            (object)array(
                                'id'           => 2,
                                'file_name'    => 'file 1',
                                'content_type' => 'image/png',
                                'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                                'inline'       => true,
                            ),
                            (object)array(
                                'id'           => 3,
                                'file_name'    => 'file 3',
                                'content_type' => 'image/png',
                                'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/nologo.png',
                            ),
                        ),
                    ),
                    (object)array(
                        'id'          => 2,
                        'author_id'   => 2,
                        'body'        => 'Reply #2',
                        'public'      => true,
                        'created_at'  => $date4->format('Y-m-d H:i:s'),
                        'attachments' => array(),
                    ),
                    (object)array(
                        'id'          => 3,
                        'author_id'   => null,
                        'body'        => 'Reply #3',
                        'public'      => true,
                        'created_at'  => $date4->format('Y-m-d H:i:s'),
                        'attachments' => array(),
                    ),
                    (object)array(
                        'id'          => 4,
                        'author_id'   => 3,
                        'body'        => 'Reply #4',
                        'public'      => true,
                        'created_at'  => $date4->format('Y-m-d H:i:s'),
                        'attachments' => array(),
                    ),
                ),
            ))
            ->addTicketCommentsFindAllResponse((object)array(
                'comments' => array(
                    (object)array(
                        'id'          => 1,
                        'body'        => 'Comment 1',
                        'author_id'   => 1,
                        'created_at'  => $date2->format('Y-m-d H:i:s'),
                    ),
                    (object)array(
                        'id'          => 2,
                        'body'        => 'Comment 1',
                        'author_id'   => 3,
                        'created_at'  => $date4->format('Y-m-d H:i:s'),
                    ),
                ),
            ))
            ->addTicketCommentsFindAllResponse((object)array(
                'comments' => array(),
            ))
            ->addTicketCommentsFindAllResponse((object)array(
                'comments' => array(),
            ))
            ->addArticleCategoriesFindAll((object)array(
                'categories' => array(
                    (object)array(
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
                    )
                )
            ))
            ->addArticleSectionsFindAll((object)array(
                'sections' => array(
                    (object)array(
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
                    )
                )
            ))
            ->addArticleSectionAccessPolicyFindResponse((object)array(
                'access_policy' => (object)array(
                    'viewable_by'                    => ArticleCategories::VIEWABLE_BY_SIGNED,
                    'manageable_by'                  => ArticleCategories::VIEWABLE_BY_STAFF,
                    'restricted_to_group_ids'        => array(),
                    'restricted_to_organization_ids' => array(),
                    'required_tags'                  => array(),
                ),
            ))
            ->addArticlesIncrementalExportResponse((object)array(
                'articles' => array(
                    (object)array(
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
                    (object)array(
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
                    (object)array(
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
            ->addArticleCommentsFindAllResponse((object)array(
                'comments' => array(
                    (object)array(
                        'id'         => 1,
                        'body'       => 'Comment 1',
                        'author_id'  => 2,
                        'created_at' => $date3->format('Y-m-d H:i:s'),
                    ),
                    (object)array(
                        'id'         => 2,
                        'body'       => 'Comment 2',
                        'author_id'  => 3,
                        'created_at' => $date4->format('Y-m-d H:i:s'),
                    ),
                )
            ))
            ->addArticleCommentsFindAllResponse((object)array(
                'comments' => array(
                    (object)array(
                        'id'         => 3,
                        'body'       => 'Comment 3',
                        'author_id'  => 2,
                        'created_at' => $date3->format('Y-m-d H:i:s'),
                    ),
                    (object)array(
                        'id'         => 4,
                        'body'       => 'Comment 4',
                        'author_id'  => 3,
                        'created_at' => $date4->format('Y-m-d H:i:s'),
                    ),
                )
            ))
            ->addArticleCommentsFindAllResponse((object)array(
                'comments' => array()
            ))
            ->addArticleAttachmentsFindAllResponse((object)array(
                'article_attachments' => array(
                    (object)array(
                        'id'           => 1,
                        'file_name'    => 'file 1',
                        'content_type' => 'image/png',
                        'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                    ),
                ),
            ))
            ->addArticleAttachmentsFindAllResponse((object)array(
                'article_attachments' => array(
                    (object)array(
                        'id'           => 1,
                        'file_name'    => 'file 1',
                        'content_type' => 'image/png',
                        'content_url'  => 'http://deskpro.com/assets/build/img/deskpro/logo.png',
                    ),
                ),
            ))
            ->addArticleAttachmentsFindAllResponse((object)array(
                'article_attachments' => array()
            ))
            ->addArticleTranslationsFindAllResponse((object)array(
                'translations' => array(
                    (object)array(
                        'id'     => '1',
                        'locale' => 'es',
                        'title'  => 'Title (es_ES)',
                        'body'   => 'Content (es_ES)',
                        'draft'  => true,
                    ),
                    (object)array(
                        'id'     => '2',
                        'locale' => 'de',
                        'title'  => 'Title (de)',
                        'body'   => 'Content (de)',
                        'draft'  => false,
                    ),
                ),
            ))
            ->addArticleTranslationsFindAllResponse((object)array(
                'translations' => array(),
            ))
            ->addArticleTranslationsFindAllResponse((object)array(
                'translations' => array(),
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
                        'tags'            => array('Tag 1', 'Tag 2'),
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
                        'tags'            => array('Tag 2', 'Tag 3'),
                    ),
                    (object)array(
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
            ->addPeopleFindResponse((object)array(
                'users' => array()
            ))
            ->addOrganizationFindResponse((object)array(
                'organization' => (object)array(
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

        $this->assertContains('Entity `organization` is not supported', $output);
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
    }

    private function checkDbTicketsData()
    {
        /** @var Entity\Ticket[] $tickets */
        $tickets = $this->ticket_repository->findAll();
        $this->assertCount(3, $tickets);

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

        $person = $this->person_repository->findOneByEmail('imported.user.100000@example.com');
        $this->assertTrue($person->isDisabled());
        $this->assertFalse($person->isDeleted());

        $person = $this->person_repository->findOneByEmail('imported.user.200000@example.com');
        $this->assertTrue($person->isDisabled());
        $this->assertFalse($person->isDeleted());
    }
}
