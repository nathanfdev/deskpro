<?php

namespace DpIntegrationTests\DeskPRO\Import;

use Application\ImportBundle\Command\CheckExportCommand;
use Application\ImportBundle\Command\ExportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ImportCsvTest
 * @package DpIntegrationTests\DeskPRO\Import
 */
class CsvTest extends \DpIntegrationTestCase
{
    public function runBefore()
    {
        $this->helper->enableDatabaseSet('EmptyDb');
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
    }

    public function testExport()
    {
        $output_path = dp_get_data_dir() . '/import/csv/export';
        if (!is_dir($output_path)) {
            mkdir($output_path, 0755, true);
        }

        $this->helper->amInPath($output_path);
        $this->helper->cleanDir($output_path);

        $application = new Application($this->helper->getSymfonyContainer()->getKernel());
        $application->add(new ExportCommand());

        $command = $application->find('dp:export:run');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'       => $command->getName(),
            'script'        => 'csv',
            '--input-path'  => DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/csv',
            '--output-path' => $output_path,
            '--batch'       => true,
        ));

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
}
