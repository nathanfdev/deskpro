<?php

namespace DeskPRO\Bundle\ImportBundle\Parser;

use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;

/**
 * Class Parser.
 */
class Parser
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param Serializer      $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(Serializer $serializer, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger     = $logger;
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
