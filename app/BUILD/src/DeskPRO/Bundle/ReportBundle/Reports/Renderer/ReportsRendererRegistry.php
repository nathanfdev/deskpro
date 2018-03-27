<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReportsRendererRegistry.
 */
class ReportsRendererRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $renderers = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @param string $outputFormat
     * @param string $id
     *
     * @return $this
     */
    public function addRenderer($type, $outputFormat, $id)
    {
        $this->renderers[$type][$outputFormat] = $id;

        return $this;
    }

    /**
     * @param string $type
     * @param string $outputFormat
     *
     * @throws \Exception
     *
     * @return ReportsRendererInterface
     */
    public function getRenderer($type, $outputFormat)
    {
        $type         = strtolower($type);
        $outputFormat = strtolower($outputFormat);

        if (isset($this->renderers[$type][$outputFormat])) {
            return $this->container->get($this->renderers[$type][$outputFormat]);
        } else {
            throw new \Exception("Unknown $outputFormat report renderer of $type type specified.");
        }
    }
}
