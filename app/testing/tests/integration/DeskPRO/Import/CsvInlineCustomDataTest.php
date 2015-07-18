<?php

namespace DpIntegrationTests\DeskPRO\Import;

use Application\ImportBundle\Command\ExportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CsvInlineCustomDataTest
 * @package DpIntegrationTests\DeskPRO\Import
 *
 * @group importer
 */
class CsvInlineCustomDataTest extends \DpIntegrationTestCase
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
        $this->input_path  = DP_ROOT . '/src/Application/ImportBundle/Resources/docs/data_example/csv_inline_custom_data';
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
