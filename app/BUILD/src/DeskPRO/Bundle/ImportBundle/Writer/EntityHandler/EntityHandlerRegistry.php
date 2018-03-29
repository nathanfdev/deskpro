<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityHandlerRegistry.
 */
class EntityHandlerRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $handlers = [];

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
     * @param array $handlers
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param PrimaryImportModelInterface $model
     *
     * @throws \Exception
     *
     * @return EntityHandlerInterface
     */
    public function getHandler(PrimaryImportModelInterface $model)
    {
        $modelClass = get_class($model);
        if (!isset($this->handlers[$modelClass])) {
            throw new \Exception("Importer entity handler with $modelClass not found");
        }

        return $this->container->get($this->handlers[$modelClass]);
    }

    /**
     * @return string[]
     */
    public function getModelClasses()
    {
        return array_keys($this->handlers);
    }

    /**
     * @param string $modelClass
     *
     * @return string
     */
    public function getTypeByModelClass($modelClass)
    {
        $modelClass = (new \ReflectionClass($modelClass))->getShortName();
        $type       = Strings::camelCaseToUnderscore($modelClass);

        return $type;
    }
}
