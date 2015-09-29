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

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\LoggerAwareInterface;
use Application\ImportBundle\Generator\ProgressBarAwareInterface;
use Application\ImportBundle\Reader\ReaderInterface;
use Exception;

/**
 * Base generator exporter.
 *
 * Class AbstractExporter
 */
abstract class AbstractExporter extends AbstractGenerator implements ExporterInterface
{
    /**
     * @var Parser\Collection
     */
    private $parsers;

    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * Constructor.
     *
     * @param Parser\Collection $parsers
     * @param ReaderInterface   $reader
     */
    public function __construct(Parser\Collection $parsers, ReaderInterface $reader)
    {
        $this->parsers = $parsers;
        $this->reader  = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByType($type)
    {
        $parser = $this->getParserByType($type);
        if ($parser instanceof Parser\NotSupportedInterface) {
            return 0;
        }

        return $parser->getCount();
    }

    /**
     * {@inheritdoc}
     */
    public function exportByType($type)
    {
        $this->logInfo(sprintf('Parsing `%s` entities', $type));

        $parser = $this->getParserByType($type);
        if ($parser instanceof Parser\NotSupportedInterface) {
            $this->logNotice(sprintf('Entity `%s` is not supported', $type));

            return new Entity\Collection();
        }

        $this->logDebug('Type: '.get_class($parser));

        return $parser->export();
    }

    /**
     * Get parser by record type.
     *
     * @param string $type
     *
     * @throws Exception
     *
     * @return Parser\ParserInterface
     */
    protected function getParserByType($type)
    {
        if (!$this->config) {
            throw new Exception('Generator configuration is not set up');
        }

        $parser = $this->parsers->getByEntityType($type);
        $parser->setConfig($this->config);

        if ($this->logger && $parser instanceof LoggerAwareInterface) {
            $parser->setLogger($this->logger);
        }
        if ($this->progress_bar && $parser instanceof ProgressBarAwareInterface) {
            $parser->setProgressBarHelper($this->progress_bar);
        }

        return $parser;
    }

    /**
     * @return array
     */
    public static function getOrderedTypes()
    {
        return array(
            Entity\EntityInterface::TYPE_ORGANIZATION_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_TICKET_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_PERSON_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_ARTICLE_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_FEEDBACK_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_ORGANIZATION,
            Entity\EntityInterface::TYPE_TICKET,
            Entity\EntityInterface::TYPE_ARTICLE_CATEGORY,
            Entity\EntityInterface::TYPE_ARTICLE,
            Entity\EntityInterface::TYPE_DOWNLOAD,
            Entity\EntityInterface::TYPE_FEEDBACK,
            Entity\EntityInterface::TYPE_NEWS,
            Entity\EntityInterface::TYPE_PERSON,
        );
    }
}
