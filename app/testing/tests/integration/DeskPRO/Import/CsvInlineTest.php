<?php

namespace DpIntegrationTests\DeskPRO\Import;

use Application\ImportBundle\Command\ExportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CsvInlineTest
 * @package DpIntegrationTests\DeskPRO\Import
 *
 * @group importer
 */
class CsvInlineTest extends \DpIntegrationTestCase
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
     * {@inheritdoc}
     */
    public function runBefore()
    {
        $this->input_path  = DP_ROOT . '/src/Application/ImportBundle/Resources/example/csv_inline';
        $this->output_path = dp_get_data_dir() . '/import/csv/export';

        if ( ! is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);
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

        $output = $command_tester->getDisplay();

        // Checking for people
        $this->assertContains('Entity `person_joe.smith@example.com` parsed successfully!', $output);
        $this->assertContains('Entity `person_angry.customer@example.com` parsed successfully!', $output);
        $this->assertContains('Entity `person_some@email.tld` parsed successfully!', $output);
        $this->assertContains('Custom field of entity `person_joe.smith@example.com` parsed successfully!', $output);

        $this->helper->seeFileFound('1/people/person_some@email.tld.json');
        $this->helper->seeInThisFile('Some Customer');

        $this->helper->seeFileFound('1/people/person_joe.smith@example.com.json');
        $this->helper->seeInThisFile('Joe Smith');

        $person = $this->getContent('1/people/person_joe.smith@example.com.json');
        $this->assertCount(5, $person['custom_fields']);

        $person = $this->getContent('1/people/person_angry.customer@example.com.json');
        $this->assertCount(2, $person['custom_fields']);

        // Checking for tickets
        $this->assertContains('Entity `ticket_144` parsed successfully!', $output);
        $this->assertContains('Entity `ticket_145` parsed successfully!', $output);

        $ticket = $this->getContent('1/tickets/ticket_144.json');
        $this->assertCount(4, $ticket['custom_fields']);

        $ticket = $this->getContent('1/tickets/ticket_145.json');
        $this->assertCount(5, $ticket['custom_fields']);

        // Checking for articles
        $this->assertContains('Entity `article_num_0` parsed successfully!', $output);
        $this->assertContains('Entity `article_num_1` parsed successfully!', $output);

        $article = $this->getContent('1/articles/article_num_0.json');
        $this->assertCount(2, $article['custom_fields']);

        $article = $this->getContent('1/articles/article_num_1.json');
        $this->assertCount(2, $article['custom_fields']);

        // Checking for feedback
        $this->assertContains('Entity `feedback_1` parsed successfully!', $output);

        $feedback = $this->getContent('1/feedback/feedback_1.json');
        $this->assertCount(2, $feedback['custom_fields']);

        // Checking for organizations
        $this->assertContains('Entity `organization_some_organization` parsed successfully!', $output);

        $organization = $this->getContent('1/organizations/organization_some_organization.json');
        $this->assertCount(2, $organization['custom_fields']);
        $this->assertCount(1, $organization['contact_data']);
    }

    /**
     * @param string $filename
     * @return array
     */
    private function getContent($filename)
    {
        return json_decode(file_get_contents($filename), true);
    }
}
