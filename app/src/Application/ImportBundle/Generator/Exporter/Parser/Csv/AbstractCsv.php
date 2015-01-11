<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\CsvReader\CsvConfig;
use Application\ImportBundle\CsvReader\CsvReaderInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParser;

/**
 * Class AbstractCsv
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
abstract class AbstractCsv extends AbstractParser
{
    /**
     * @var CsvReaderInterface
     */
    protected $csv_reader;

    /**
     * Constructor
     *
     * @param CsvReaderInterface $csv_reader
     */
    public function __construct(CsvReaderInterface $csv_reader)
    {
        $this->csv_reader = $csv_reader;
    }

    /**
     * Get absolute file path
     *
     * @param string $record_type
     * @return CsvConfig
     */
    protected function getCsvReaderConfig($record_type)
    {
        return new CsvConfig(sprintf('%s/%s', $this->config->getInputPath(), $record_type));
    }
}
