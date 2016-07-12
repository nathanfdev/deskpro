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

namespace Application\ImportBundle\Generator\Writer\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Writer\AbstractWriter;

/**
 * Generator json writer.
 *
 * Class JsonWriter
 */
final class JsonWriter extends AbstractWriter
{
    /**
     * @var DestinationCollection|Destination[]
     */
    private $mapping;

    /**
     * Constructor.
     *
     * @param DestinationCollection $mapping
     */
    public function __construct(DestinationCollection $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_JSON;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->createOutputDirIfNotExist();
        $this->createOutputEntityDirsIfNotExist($this->entity_types);
    }

    /**
     * {@inheritdoc}
     */
    public function writeData(Entity\EntityInterface $entity)
    {
        if (!$this->config) {
            throw new \RuntimeException('Generator configuration is not set up');
        }

        $data = $entity->toArray();
        $path = $this->getEntityPath($entity);

        if (!$this->writeJsonFile($data, $path)) {
            throw new \RuntimeException(sprintf('Unable to write JSON file %s', $path));
        }

        return true;
    }

    /**
     * Make output batch entity directories if not exist.
     *
     * @param array $entity_types
     *
     * @throws \RuntimeException
     */
    private function createOutputEntityDirsIfNotExist(array $entity_types)
    {
        foreach ($this->mapping as $destination) {
            if (in_array($destination->getEntityType(), $entity_types, true)) {
                $path = $this->getDestinationOutputPath($destination);

                if (is_dir($path) === false) {
                    if (mkdir($path, 0777, true) === false) {
                        throw new \RuntimeException(sprintf('Unable to create output dir `%s`', $path));
                    }
                }
            }
        }
    }

    /**
     * Returns batch output path.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    private function getBatchOutputPath()
    {
        if (!$this->config) {
            throw new \RuntimeException('Generator configuration is not defined');
        }

        $id = $this->batch_config && $this->batch_config->getId()
            ? $this->batch_config->getId()
            : 1;

        return $this->config->getOutputPath().$id.'/';
    }

    /**
     * Returns destination path.
     *
     * @param Destination $destination
     *
     * @return string
     */
    private function getDestinationOutputPath(Destination $destination)
    {
        return $this->getBatchOutputPath().$destination->getEntityOutputPath();
    }

    /**
     * Returns the entity file path.
     *
     * @param Entity\EntityInterface $entity
     *
     * @throws \Exception
     *
     * @return string
     */
    private function getEntityPath(Entity\EntityInterface $entity)
    {
        $destination = $this->mapping->getByEntityType($entity->getType());

        return $this->getDestinationOutputPath($destination).'/'.$entity->getDestination().'.json';
    }
}
