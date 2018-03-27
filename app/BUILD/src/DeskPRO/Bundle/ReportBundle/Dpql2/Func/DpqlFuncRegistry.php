<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DpqlFuncFactory.
 */
class DpqlFuncRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DpqlFunctionInterface[]
     */
    private $functions = [];

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
    public function addFunction($name, $id)
    {
        $this->functions[$name] = $id;

        return $this;
    }

    /**
     * Returns the correct function handler object.
     *
     * @param string $name
     *
     * @return DpqlFunctionInterface
     */
    public function getFunction($name)
    {
        $name = strtoupper($name);
        if (isset($this->functions[$name])) {
            return $this->container->get($this->functions[$name]);
        } else {
            return new DpqlSqlPass($name);
        }
    }

    /**
     * @return DpqlLink
     */
    public function getLinkFunction()
    {
        return $this->getFunction('dpql_link');
    }
}
