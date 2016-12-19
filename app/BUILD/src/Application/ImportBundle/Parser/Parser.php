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

namespace Application\ImportBundle\Parser;

use Application\ImportBundle\Importer\ImporterContext;
use Application\ImportBundle\Model\PrimaryImportModelInterface;
use Application\ImportBundle\Parser\Reader\JsonReader;
use Application\ImportBundle\Parser\Reader\NotFoundException;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;

/**
 * Parser from json files.
 */
class Parser implements ParserInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var JsonReader
     */
    private $reader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param JsonReader      $reader
     * @param Serializer      $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(JsonReader $reader, Serializer $serializer, LoggerInterface $logger)
    {
        $this->reader     = $reader;
        $this->serializer = $serializer;
        $this->logger     = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByType(ImporterContext $context, $modelClass)
    {
        try {
            return $this->reader->getCount($context->getInputPath(), $modelClass, $context->getBatchConfig()->getId());
        } catch (NotFoundException $e) {
            $this->logger->info("$modelClass was not found.");
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function exportByType(ImporterContext $context, $modelClass)
    {
        $models = [];

        try {
            $data = $this->reader->getData($context->getInputPath(), $modelClass, $context->getBatchConfig()->getId());
            foreach ($data as $oid => $rawData) {
                $model = $this->exportRawData($oid, $rawData, $modelClass);
                if ($model) {
                    $models[$oid] = $model;
                }
            }
        } catch (NotFoundException $e) {
            $this->logger->info("$modelClass was not found.");
        }

        return $models;
    }

    /**
     * @param string $oid
     * @param mixed  $rawData
     * @param string $modelClass
     *
     * @return PrimaryImportModelInterface
     */
    public function exportRawData($oid, $rawData, $modelClass)
    {
        try {
            $this->logger->debug("Export $modelClass#$oid");

            if (is_array($rawData)) {
                /** @var PrimaryImportModelInterface $model */
                $model = $this->serializer->fromArray($rawData, $modelClass);
            } else {
                /** @var PrimaryImportModelInterface $model */
                $model = $this->serializer->deserialize($rawData, $modelClass, 'json');
            }

            $model->setOid($oid);
            $model->setRawData($rawData);

            return $model;
        } catch (\Exception $e) {
            $this->logger->error('Unable to parse entity.');
            $this->logger->error($e->getMessage());

            return false;
        }
    }
}
