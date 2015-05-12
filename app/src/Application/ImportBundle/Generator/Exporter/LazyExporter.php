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

use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Reader\BaseConfig;
use Exception;

/**
 * Exporter proxy to lazy load createExporter() method when the importer is initialized
 *
 * Class LazyExporter
 * @package Application\ImportBundle\Generator\Exporter
 */
final class LazyExporter implements ExporterInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ExporterInterface
     */
    private $instance;

    /**
     * @param AbstractFactory $factory
     */
    public function __construct(AbstractFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        // Getting type from a mock object (with out initialize of an exporter reader)
        $exporter_class = str_replace('Factory', '', get_class($this->factory));
        if ( ! class_exists($exporter_class)) {
            throw new Exception(sprintf('Exporter `%s` not found', $exporter_class));
        }

        /** @var ExporterInterface $exporter */
        $exporter = new $exporter_class(new Parser\Collection());
        return $exporter->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(GeneratorConfig $config)
    {
        throw new Exception('Use initialize method to get instance');
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByType($type)
    {
        throw new Exception('Use initialize method to get instance');
    }

    /**
     * {@inheritdoc}
     */
    public function exportByType($type)
    {
        throw new Exception('Use initialize method to get instance');
    }

    /**
     * Lazy loading when any interface method was called
     *
     * @param BaseConfig $config
     * @return ExporterInterface
     */
    public function initialize(BaseConfig $config)
    {
        if ($this->instance === null) {
            $this->instance = $this->factory->createExporter($config);
        }

        return $this->instance;
    }
}
