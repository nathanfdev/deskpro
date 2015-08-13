<?php

namespace DpIntegrationTests\DeskPRO\Import;

/**
 * Class OsTicketTest
 * @package DpIntegrationTests\DeskPRO\Import
 *
 * @group importer
 */
class OsTicketTest extends \DpIntegrationTestCase
{
    /**
     * @var string
     */
    private $output_path;

    /**
     * {@inheritdoc}
     */
    public function runBefore()
    {
        $this->helper->enableFreshDatabaseSet('FreshDb');

        $entity_manager = $this->helper->getSymfonyContainer()->getEm();
        $entity_manager->clear();

        $this->output_path = dp_get_data_dir() . '/import/osticket/export';
        if ( ! is_dir($this->output_path)) {
            mkdir($this->output_path, 0755, true);
        }

        $this->helper->amInPath($this->output_path);
        $this->helper->cleanDir($this->output_path);
    }

    public function testCheck()
    {

    }

    public function testExport()
    {

    }

    public function testImport()
    {

    }

    public function testImportBatch()
    {

    }
}
