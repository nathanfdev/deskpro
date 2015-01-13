<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\ImportBundle\Generator\Writer\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Writer\AbstractWriter;
use Exception;

/**
 * Generator json writer
 *
 * Class JsonWriter
 * @package Application\ImportBundle\Generator\Writer\Json
 */
final class JsonWriter extends AbstractWriter
{
    /**
     * @var Destination\Collection
     */
    private $mapping;

    /**
     * Constructor
     *
     * @param Destination\Collection $mapping
     */
    public function __construct(Destination\Collection $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function writeData(Entity\EntityInterface $entity)
    {
        if (!$this->config) {
            throw new Exception('Generator configuration is not set up');
        }

        $this->createOutputDirsIfNotExist();
        file_put_contents($this->getEntityPath($entity), json_encode($entity->toArray()));

        return true;
    }

    /**
     * Returns the entity file path
     *
     * @param Entity\EntityInterface $entity
     *
     * @return string
     * @throws \Exception
     */
    private function getEntityPath(Entity\EntityInterface $entity)
    {
        foreach ($this->mapping as $destination) {
            /** @var Destination\DestinationInterface $destination */
            if ($entity->getType() === $destination->getEntityType()) {
                return $this->getDestinationOutputPath($destination) . $entity->getDestination();
            }
        }

        throw new Exception(sprintf('Entity `%s` not supported', get_class($entity)));
    }

    /**
     * Make output directories if not exist
     *
     * @throws \Exception
     */
    private function createOutputDirsIfNotExist()
    {
        if (!$this->config->getOutputPath()) {
            throw new Exception('Output path is not defined');
        }
        if (!is_dir($this->config->getOutputPath())) {
            if (!mkdir($this->config->getOutputPath(), 0777, true)) {
                throw new Exception(sprintf('Unable to create output dir `%s`', $this->config->getOutputPath()));
            }
        }

        foreach ($this->mapping as $destination) {
            /** @var Destination\DestinationInterface $destination */
            $path = $this->getDestinationOutputPath($destination);

            if (!is_dir($path)) {
                if (!mkdir($path, 0777, true)) {
                    throw new Exception(sprintf('Unable to create output dir `%s`', $path));
                }
            }
        }
    }

    /**
     * Returns destination path
     *
     * @param Destination\DestinationInterface $destination
     * @return string
     */
    private function getDestinationOutputPath(Destination\DestinationInterface $destination)
    {
        return $this->config->getOutputPath() . $destination->getEntityOutputPath();
    }
}
