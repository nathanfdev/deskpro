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

namespace Application\ImportBundle\Reader\Csv;

use Application\ImportBundle\Reader\BaseReader;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Orb\Util\Arrays;
use SplFileObject;
use LimitIterator;

/**
 * Csv data parser
 *
 * Class CsvReader
 * @package Application\ImportBundle\Reader\Csv
 */
class CsvReader extends BaseReader implements CsvReaderInterface
{
    /**
     * Constructor
     *
     * @param CsvConfig $config
     */
    public function __construct(CsvConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowsCount(CsvConfig $config)
    {
        $this->detectDelimiter($config);
        $iterator = $this->getIterator($config);

        $count = 0;
        foreach ($iterator as $row) {
            if (is_array($row)) {
                $count++;
            }
        }

        // remove header from count value
        if ($count > 0) {
            $count--;
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(CsvConfig $config)
    {
        $this->detectDelimiter($config);
        $iterator = $this->getIterator($config);

        $header = null;
        $data   = array();
        foreach ($iterator as $row) {
            if ( ! $this->isValidRow($row)) {
                continue;
            }

            if ($header === null) {
                $header = $row;
            } else {
                $record = array();
                foreach ($header as $num => $key) {
                    if (array_key_exists($num, $row) === false) {
                        throw new CsvReaderException(sprintf(
                            'Row `%s` does not have key `%s`',
                            @json_encode($record), $key
                        ));
                    }

                    $record[$key] = $row[$num];
                }

                $data[] = $record;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isReady()
    {
        return true;
    }

    /**
     * Returns spl file object iterator
     *
     * @param CsvConfig $config
     *
     * @return LimitIterator
     * @throws \Exception
     */
    private function getIterator(CsvConfig $config)
    {
        if ( ! stream_is_local($config->getResource())) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $config->getResource()));
        }

        if ( ! file_exists($config->getResource())) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $config->getResource()));
        }

        try {
            $file = new SplFileObject($config->getResource(), 'rb');
        } catch (\RuntimeException $e) {
            throw new NotFoundResourceException(sprintf('Error opening file "%s".', $config->getResource()), 0, $e);
        }

        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($config->getDelimiter(), $config->getEnclosure(), $config->getEscape());

        return new LimitIterator($file);
    }

    /**
     * Detect a delimiter
     *
     * @param CsvConfig $config
     */
    private function detectDelimiter(CsvConfig $config)
    {
        $delimiters = array_diff(array(';', ','), array($config->getDelimiter()));

        while (true) {
            $iterator = $this->getIterator($config);
            $iterator->rewind();

            $row = $iterator->current();
            if ($this->isValidRow($row) || empty($delimiters)) {
                return;
            }

            $config->setDelimiter(array_shift($delimiters));
        }

    }

    /**
     * Checks if row is array
     *
     * @param mixed $row
     * @return bool
     */
    private function isValidRow($row)
    {
        return is_array($row) && count(Arrays::removeEmptyString($row)) > 1;
    }
}
