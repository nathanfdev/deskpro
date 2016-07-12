<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\Csv;

use Application\ImportBundle\Reader\AbstractReader;
use Application\ImportBundle\Reader\NotFoundException;
use LimitIterator;
use SplFileObject;

/**
 * Csv data parser.
 *
 * Class CsvReader
 *
 * @property CsvConfig $config
 */
class CsvReader extends AbstractReader implements CsvReaderInterface
{
    /**
     * Constructor.
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
    public function checkConfig()
    {
        $files = [
            self::FILE_ARTICLE_CATEGORIES => [],
            self::FILE_ARTICLES           => [
                self::FILE_ARTICLE_CUSTOM_FIELDS,
            ],
            self::FILE_DOWNLOADS => [
                self::FILE_DOWNLOAD_ATTACHMENTS,
            ],
            self::FILE_FEEDBACK => [
                self::FILE_FEEDBACK_ATTACHMENTS,
                self::FILE_FEEDBACK_CUSTOM_FIELDS,
            ],
            self::FILE_NEWS   => [],
            self::FILE_PEOPLE => [
                self::FILE_PEOPLE_CONTACT_DATA,
                self::FILE_PEOPLE_CUSTOM_FIELDS,
            ],
            self::FILE_TICKETS => [
                self::FILE_TICKET_MESSAGES,
                self::FILE_TICKET_ATTACHMENTS,
                self::FILE_TICKET_CUSTOM_FIELDS,
            ],
            self::FILE_ORGANIZATIONS => [
                self::FILE_ORGANIZATION_CONTACT_DATA,
                self::FILE_ORGANIZATION_CUSTOM_FIELDS,
            ],
        ];

        if (!is_dir($this->config->getPath())) {
            throw new \RuntimeException(sprintf('`%s` is not a directory.', $this->config->getPath()));
        }

        $has_primary_iterator = false;
        foreach ($files as $primary_file => $related_files) {
            try {
                $this->getIterator($primary_file);
                $has_primary_iterator = true;
            } catch (NotFoundException $e) {
                foreach ($related_files as $file) {
                    try {
                        $this->getIterator($file);
                        throw new \RuntimeException(sprintf('Unable to parse `%s` without primary file `%s`.', $file, $primary_file));
                    } catch (NotFoundException $e) {
                        // File not found, continue...
                    }
                }
            }
        }

        if (!$has_primary_iterator) {
            throw new \RuntimeException(sprintf(
                'No required files found in directory `%s`. Expected one of %s.',
                $this->config->getPath(), implode(', ', array_map(
                    function ($file) {
                        return '`'.$file.'`';
                    },
                    $files
                ))
            ));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowsCount($entity_type)
    {
        $this->detectDelimiter($entity_type);
        $iterator = $this->getIterator($entity_type);

        $count = 0;
        foreach ($iterator as $row) {
            if (is_array($row)) {
                ++$count;
            }
        }

        // remove header from count value
        if ($count > 0) {
            --$count;
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($entity_file)
    {
        $this->detectDelimiter($entity_file);
        $iterator = $this->getIterator($entity_file);

        $header = null;
        $data   = [];
        foreach ($iterator as $row) {
            if (!$this->isValidRow($row)) {
                continue;
            }

            if ($header === null) {
                $header = $row;
            } else {
                $record = [];
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
     * Returns entity type path.
     *
     * @param string $entity_file
     *
     * @return string
     */
    private function getEntityPath($entity_file)
    {
        return rtrim($this->config->getPath(), '/').'/'.$entity_file;
    }

    /**
     * Returns spl file object iterator.
     *
     * @param string $entity_type
     *
     * @throws \RuntimeException
     *
     * @return LimitIterator
     */
    private function getIterator($entity_type)
    {
        $entity_path = $this->getEntityPath($entity_type);

        if (!file_exists($entity_path)) {
            throw new NotFoundException(sprintf('File "%s" not found.', $entity_path));
        }
        if (!stream_is_local($entity_path)) {
            throw new \RuntimeException(sprintf('This is not a local file "%s".', $entity_path));
        }

        try {
            $file = new SplFileObject($entity_path, 'rb');
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Error opening file "%s".', $entity_path), 0, $e);
        }

        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($this->config->getDelimiter(), $this->config->getEnclosure(), $this->config->getEscape());

        return new LimitIterator($file);
    }

    /**
     * Detect a delimiter.
     *
     * @param string $entity_file
     */
    private function detectDelimiter($entity_file)
    {
        $delimiters = array_diff([';', ','], [$this->config->getDelimiter()]);

        while (true) {
            $iterator = $this->getIterator($entity_file);
            $iterator->rewind();

            $row = $iterator->current();
            if ($this->isValidRow($row) || empty($delimiters)) {
                return;
            }

            $this->config->setDelimiter(array_shift($delimiters));
        }
    }

    /**
     * Checks if row is array.
     *
     * @param mixed $row
     *
     * @return bool
     */
    private function isValidRow($row)
    {
        return is_array($row) && count($row) > 1;
    }
}
