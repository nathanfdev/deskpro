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
 * Class JsonTest.
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
     * @var EntityRepository\Blob
     */
    private $blob_repository;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $object_lang_repository;

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
        $this->helper->loadFixtures('Import/Languages');
        $this->helper->loadFixtures('Import/Article');

        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $entity_manager->clear();

        $this->ticket_repository              = $entity_manager->getRepository('DeskPRO:Ticket');
        $this->ticket_attachment_repository   = $entity_manager->getRepository('DeskPRO:TicketAttachment');
        $this->person_repository              = $entity_manager->getRepository('DeskPRO:Person');
        $this->news_repository                = $entity_manager->getRepository('DeskPRO:News');
        $this->article_repository             = $entity_manager->getRepository('DeskPRO:Article');
        $this->article_category_repository    = $entity_manager->getRepository('DeskPRO:ArticleCategory');
        $this->feedback_repository            = $entity_manager->getRepository('DeskPRO:Feedback');
        $this->feedback_attachment_repository = $entity_manager->getRepository('DeskPRO:FeedbackAttachment');
        $this->download_repository            = $entity_manager->getRepository('DeskPRO:Download');
        $this->organization_repository        = $entity_manager->getRepository('DeskPRO:Organization');
        $this->blob_repository                = $entity_manager->getRepository('DeskPRO:Blob');
        $this->object_lang_repository         = $entity_manager->getRepository('DeskPRO:ObjectLang');

        $this->input_path  = DP_ROOT.'/src/Application/ImportBundle/Resources/example/json';
        $this->output_path = dp_get_data_dir().'/import/json/export';

        if (!is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->seeFileFound($this->input_path);
        $this->helper->seeFileFound($this->output_path);

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);

        if (file_exists($this->input_path.'/input.batch.json')) {
            $this->helper->deleteFile($this->input_path.'/input.batch.json');
        }

        $this->overrideDpRootPath('/1/downloads/download1.json');
        $this->overrideDpRootPath('/1/feedback/feedback1.json');
        $this->overrideDpRootPath('/1/articles/article1.json');
    }

    public function testCheck()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new CheckExportCommand());

        $command        = $application->find('dp:export:check');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'json',
            '--input-path' => $this->input_path,
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $output = $command_tester->getDisplay();

        // Checking for tickets
        $this->assertContains('[JSONTicketMessage #1 (message_1)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONTicket #1 (ticket_1)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONTicketMessage #1 (message_1)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONTicket #2 (ticket_2)] Entity parsed successfully!', $output);

        // Checking for people
        $this->assertContains('[JSONPerson #710618382 (person_710618382)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONPerson #710618383 (person_710618383)] Entity parsed successfully!', $output);

        // Checking for news
        $this->assertContains('[JSONNews #1 (news_1)] Entity parsed successfully!', $output);

        // Checking for feedback
        $this->assertContains('[JSONAttachment #0 (attachment_0)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONFeedback #1 (feedback_1)] Entity parsed successfully!', $output);

        // Checking for articles
        $this->assertContains('[JSONCustomField #1 (custom_field_1)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONAttachment #0 (attachment_0)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONArticleComment #0 (article_comment_0)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONTranslation #0 (translation_0)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONTranslation #1 (translation_1)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONArticle #1 (article_1)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONArticle #2 (article_2)] Entity parsed successfully!', $output);

        // Checking for downloads
        $this->assertContains('[JSONDownload #1 (download_1)] Entity parsed successfully!', $output);

        // Checking for organizations
        $this->assertContains('[JSONContactData #1 (contact_data_1)] Entity parsed successfully!', $output);
        $this->assertContains('[JSONOrganization #Some Organization (organization_some_organization)] Entity parsed successfully!', $output);

        $this->assertContains('Done. Checking was successful.', $output);

        $this->checkNoErrors($command_tester);
        $this->checkJsonEmpty();
        $this->checkDbEmpty();
    }

    public function testExport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ExportCommand());

        $command        = $application->find('dp:export:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'json',
            '--input-path'  => $this->input_path,
            '--output-path' => $this->output_path,
            '--batch'       => true,
        ));

        $this->checkNoErrors($command_tester);
        $this->checkJsonData();
        $this->checkDbEmpty();
    }

    public function testImport()
    {
        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ImportCommand());

        $command        = $application->find('dp:import:run');
        $command_tester = new CommandTester($command);
        $command_tester->execute(array(
            'command'      => $command->getName(),
            'script'       => 'json',
            '--input-path' => $this->input_path,
            '--verbose'    => true,
            '--batch'      => true,
        ));

        $this->checkDbWriterOutput($command_tester);
        $this->checkNoErrors($command_tester);
        $this->checkJsonEmpty();
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
            'script'        => 'json',
            '--input-path'  => $this->input_path,
            '--output-path' => $this->output_path,
            '--verbose'     => true,
            '--batch'       => true,
        ));

        $this->checkDbWriterOutput($command_tester);
        $this->checkNoErrors($command_tester);
        $this->checkJsonData();
        $this->checkDbData();
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
        $this->checkJsonFile('/1/organizations/organization_some_organization.json', '1/organizations/organization_some_organization.json');
    }

    /**
     * Checking that source and generated json files are equal.
     *
     * @param string $input_file_path
     * @param string $output_file_path
     */
    private function checkJsonFile($input_file_path, $output_file_path)
    {
        $this->assertEquals(
            json_decode(file_get_contents($this->input_path.$input_file_path)),
            json_decode(file_get_contents($output_file_path))
        );
    }

    private function checkDbEmpty()
    {
        $this->assertEquals(1, $this->ticket_repository->countAll());
        $this->assertEquals(0, $this->ticket_attachment_repository->countAll());
        $this->assertEquals(3, $this->person_repository->countAll());
        $this->assertEquals(1, $this->news_repository->countAll());
        $this->assertEquals(3, $this->article_repository->countAll());
        $this->assertEquals(3, $this->article_category_repository->countAll());
        $this->assertEquals(1, $this->feedback_repository->countAll());
        $this->assertEquals(0, $this->feedback_attachment_repository->countAll());
        $this->assertEquals(0, $this->download_repository->countAll());
        $this->assertEquals(1, $this->organization_repository->countAll());
        $this->assertEquals(0, $this->blob_repository->countAll());
    }

    private function checkDbData()
    {
        $this->checkDbArticleData();
        $this->checkDbArticleCategoryData();
        $this->checkDbPeopleData();
        $this->checkDbTicketsData();
        $this->checkDbFeedbackData();
        $this->checkDbOrganizationData();
        $this->checkDbDownloadData();
        $this->checkDbNewsData();
        $this->checkDbBlobData();
    }

    private function checkDbArticleData()
    {
        $this->assertCount(3, $this->article_repository->findAll());

        /** @var Entity\Article $article */
        $article = $this->article_repository->findOneBy(array('id' => 2));
        $this->assertNotNull($article);

        $this->assertEquals('Article 1', $article->getRealTitle());
        $this->assertEquals('Content 1', $article->getContentPlain());
        $this->assertEquals('2-slug-article-1', $article->getUrlSlug());
        $this->assertEquals('published', $article->getStatusCode());
        $this->assertEquals('Sub Category 1', $article->getCategoryNames(',', false));
        $this->assertEquals('Category 1 > Sub Category 1', $article->getCategoryNames());
        $this->assertEquals(new \DateTime('2015-01-15 00:00:00'), $article->getDateCreated());
        $this->assertNull($article->getDatePublished());
        $this->assertNull($article->getDateEnd());
        $this->assertNull($article->getDateUpdated());

        $labels = array();
        foreach ($article->getLabels() as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('Label 1', 'Label 2', 'Label 3'), $labels);
        $this->assertCount(1, $article->getCustomData());

        /** @var Entity\CustomDataArticle $custom_data */
        $custom_data = $article->getCustomData()->first();
        $this->assertEquals(2, $custom_data->getArticleId());
        $this->assertEquals(1, $custom_data->getData());

        /** @var Entity\ObjectLang[] $object_langs */
        $object_langs = $this->object_lang_repository->findBy(array('ref_type' => 'articles', 'ref_id' => $article->getId()));
        $this->assertCount(2, $object_langs);

        $object_lang_1 = $object_langs[0];
        $this->assertEquals('Article 1 (es_ES)', $object_lang_1->getValue());

        $object_lang_2 = $object_langs[1];
        $this->assertEquals('Content 1 (es_ES)', $object_lang_2->getValue());
    }

    private function checkDbArticleCategoryData()
    {
        $this->assertCount(4, $this->article_category_repository->findAll());

        /** @var Entity\ArticleCategory $parent_category */
        $parent_category = $this->article_category_repository->findOneBy(array('title' => 'Category 1'));
        $this->assertNotNull($parent_category);
        $this->assertCount(2, $parent_category->getChildren());

        $children_categories = $parent_category->getChildren();

        $children_category = $children_categories[0];
        $this->assertEquals('Sub Category 1', $children_category->getRealTitle());

        $children_category = $children_categories[1];
        $this->assertEquals('Sub Category 2', $children_category->getRealTitle());
    }

    private function checkDbPeopleData()
    {
        $this->assertCount(3, $this->person_repository->findAll());

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
        $this->assertEquals(1, $ticket->getLanguageId());
        $this->assertNotNull($ticket->getAgent());
        $this->assertEquals('user@example.com', $ticket->getAgent()->getPrimaryEmail()->getEmail());

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

        /** @var Entity\Feedback $feedback */
        $feedback = $this->feedback_repository->findOneBy(array('title' => 'Feedback 1'));
        $this->assertNotNull($feedback);

        $labels = array();
        foreach ($feedback->getLabels() as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('Feedback Label 1'), $labels);
    }

    private function checkDbOrganizationData()
    {
        $this->assertCount(1, $this->organization_repository->findAll());

        /** @var Entity\Organization $organization */
        $organization = $this->organization_repository->findOneBy(array('name' => 'Some Organization'));
        $this->assertNotNull($organization);

        $contact_data1 = $organization->getContactData('mobile');
        $contact       = $contact_data1[0];
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

    private function checkDbDownloadData()
    {
        $this->assertCount(1, $this->download_repository->findAll());

        /** @var Entity\Download $download */
        $download = $this->download_repository->findOneBy(array('title' => 'Download 1'));
        $this->assertNotNull($download);

        $labels = array();
        foreach ($download->getLabels() as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('Label 1'), $labels);
    }

    private function checkDbNewsData()
    {
        $this->assertCount(2, $this->news_repository->findAll());

        /** @var Entity\News $news */
        $news = $this->news_repository->findOneBy(array('title' => 'News Title 1'));
        $this->assertNotNull($news);

        $labels = array();
        foreach ($news->getLabels() as $label) {
            $labels[] = $label->getLabel();
        }

        $this->assertEquals(array('News Label 1'), $labels);
    }

    private function checkDbBlobData()
    {
        $this->assertCount(3, $this->blob_repository->findBy(array('content_type' => 'csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'downloads.csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'feedback.csv')));
        $this->assertCount(1, $this->blob_repository->findBy(array('filename' => 'articles.csv')));
    }

    /**
     * @param CommandTester $command_tester
     */
    private function checkDbWriterOutput(CommandTester $command_tester)
    {
        $output = $command_tester->getDisplay();

        // Checking for people
        $this->assertContains('Persisted Person #2', $output);
        $this->assertContains('Persisted Person #3', $output);

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

    /**
     * @param CommandTester $command_tester
     */
    private function checkNoErrors(CommandTester $command_tester)
    {
        $output = $command_tester->getDisplay();

        $this->assertNotContains('ERROR', $output);
        $this->assertNotContains('CRITICAL', $output);
    }

    /**
     * @param string $file
     */
    private function overrideDpRootPath($file)
    {
        $dp_root = str_replace('/app', '/', DP_ROOT);
        $dp_root = str_replace('\'', '', $dp_root);
        $dp_root = str_replace('/', '\/', $dp_root);

        $content = file_get_contents($this->input_path.$file);
        $content = str_replace('\/deskpro\/www\/', $dp_root, $content);

        file_put_contents($this->input_path.$file, $content);
    }
}
