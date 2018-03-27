<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DpqlPlaceholderRegistry.
 */
class DpqlPlaceholderRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AbstractPlaceholder[]
     */
    private $placeholders = [];

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
     * @param string $name
     * @param string $id
     *
     * @return $this
     */
    public function addPlaceholder($name, $id)
    {
        $this->placeholders[$name] = $id;

        return $this;
    }

    /**
     * Returns the correct function handler object.
     *
     * @param string $name
     *
     * @throws DpqlException
     *
     * @return AbstractPlaceholder
     */
    public function getPlaceholder($name)
    {
        $name = strtoupper($name);
        $name = preg_replace('/(\w+)_(\d+)/', '\\1\\2', $name);

        if (isset($this->placeholders[$name])) {
            return $this->container->get($this->placeholders[$name]);
        } else {
            throw new DpqlException("Unknown placeholder $name specified.");
        }
    }
}
