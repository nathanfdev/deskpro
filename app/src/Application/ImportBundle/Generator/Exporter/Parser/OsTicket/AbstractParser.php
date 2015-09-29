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

namespace Application\ImportBundle\Generator\Exporter\Parser\OsTicket;

use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Reader\OsTicket\OsTicketReaderInterface;
use Exception;

/**
 * Abstract osTicket parser.
 *
 * Class AbstractParser
 */
abstract class AbstractParser extends \Application\ImportBundle\Generator\Exporter\Parser\AbstractParser
{
    const MAX_BATCH_SIZE = 1000;

    /**
     * @var OsTicketReaderInterface
     */
    protected $reader;

    /**
     * @var int
     */
    protected $entities_loaded = 0;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * Constructor.
     *
     * @param OsTicketReaderInterface $reader
     * @param FormatterInterface      $formatter
     */
    public function __construct(OsTicketReaderInterface $reader, FormatterInterface $formatter)
    {
        $this->reader    = $reader;
        $this->formatter = $formatter;
    }

    /**
     * Returns reader batch size.
     *
     * @return int
     */
    protected function getReaderBatchSize()
    {
        $batch_size = self::MAX_BATCH_SIZE;
        if ($this->getBatchConfig()->getBatchSize() < $batch_size) {
            $batch_size = $this->getBatchConfig()->getBatchSize();
        }
        if ($this->getEntitiesLeftToLoad() < $batch_size) {
            $batch_size = $this->getEntitiesLeftToLoad();
        }

        return $batch_size;
    }

    /**
     * Returns count of entities to load in a batch.
     *
     * @return int
     */
    protected function getEntitiesLeftToLoad()
    {
        return $this->getBatchConfig()->getBatchSize() - $this->entities_loaded;
    }

    /**
     * Returns batch config.
     *
     * @throws Exception
     *
     * @return BatchConfig
     */
    protected function getBatchConfig()
    {
        if ($this->config->getExporterBatchConfig()) {
            return $this->config->getExporterBatchConfig();
        }

        throw new Exception('Batch config is not defined');
    }
}
