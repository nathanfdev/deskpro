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

namespace Application\ImportBundle\Generator\Writer;

use Application\ImportBundle\Entity;
use Exception;

/**
 * Class JsonWriter
 * @package Application\ImportBundle\Generator\Writer
 */
class JsonWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function writeData(Entity\EntityInterface $entity)
    {
        if (!$this->config) {
            throw new Exception('Generator configuration is not set up');
        }
        if ($this->config->isLive()) {
            $this->createOutputDirsIfNotExist();
            file_put_contents($this->getDestinationPath($entity), json_encode($entity->toArray()));
        }

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
    private function getDestinationPath(Entity\EntityInterface $entity)
    {
        switch (true) {
            case $entity instanceof Entity\Person:
                return $this->getExportPeopleOutputPath() . $entity->getDestination();
            case $entity instanceof Entity\Ticket:
                return $this->getExportTicketsOutputPath() . $entity->getDestination();
            default:
                throw new Exception(sprintf('Entity `%s` not supported', get_class($entity)));
        }
    }

    /**
     * Directory to generated people json files
     *
     * @return string
     */
    private function getExportPeopleOutputPath()
    {
        return $this->config->getOutputPath() . 'people/';
    }

    /**
     * Directory to generated tickets json files
     *
     * @return string
     */
    private function getExportTicketsOutputPath()
    {
        return $this->config->getOutputPath() . 'tickets/';
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

        $export_paths = array(
            $this->getExportPeopleOutputPath(),
            $this->getExportTicketsOutputPath(),
        );

        foreach ($export_paths as $path) {
            if (!is_dir($path)) {
                if (!mkdir($path, 0777, true)) {
                    throw new Exception(sprintf('Unable to create output dir `%s`', $path));
                }
            }
        }
    }
}
