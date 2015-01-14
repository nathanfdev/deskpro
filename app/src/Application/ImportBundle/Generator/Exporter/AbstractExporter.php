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

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\LoggerAwareInterface;
use Application\ImportBundle\Generator\ProgressBarAwareInterface;
use Exception;

/**
 * Base data generator class methods
 *
 * Class AbstractExporter
 * @package Application\ImportBundle\Generator\Exporter
 */
abstract class AbstractExporter extends AbstractGenerator implements ExporterInterface
{
    /**
     * @var Parser\Collection
     */
    private $parsers;

    /**
     * Constructor
     *
     * @param Parser\Collection $parsers
     */
    public function __construct(Parser\Collection $parsers)
    {
        $this->parsers = $parsers;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByType($type)
    {
        return $this->getParserByType($type)->getCount();
    }

    /**
     * {@inheritdoc}
     */
    public function exportByType($type)
    {
        return $this->getParserByType($type)->export();
    }

    /**
     * Get parser by record type
     *
     * @param string $type
     *
     * @return Parser\ParserInterface
     * @throws Exception
     */
    private function getParserByType($type)
    {
        if (!$this->config) {
            throw new Exception('Generator configuration is not set up');
        }

        foreach ($this->parsers as $parser) {
            /** @var Parser\ParserInterface $parser */
            if ($parser->getRecordType() === $type) {
                $parser->setConfig($this->config);

                if ($this->logger && $parser instanceof LoggerAwareInterface) {
                    /** @var LoggerAwareInterface $parser */
                    $parser->setLogger($this->logger);
                }
                if ($this->progress_bar && $parser instanceof ProgressBarAwareInterface) {
                    /** @var ProgressBarAwareInterface $parser */
                    $parser->setProgressBarHelper($this->progress_bar);
                }

                return $parser;
            }
        }

        throw new Exception(sprintf('This record type `%s` is not supported', $type));
    }
}
