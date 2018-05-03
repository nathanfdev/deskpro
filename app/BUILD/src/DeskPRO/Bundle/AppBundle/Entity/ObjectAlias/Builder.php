<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use DeskPRO\Bundle\AppBundle\ObjectAlias;
use Doctrine\ORM;
use DeskPRO\Bundle\AppBundle\Entity\AppStore;

class Builder
{
    /** @var ORM\EntityManager  */
    private $entityManager;

    /** @var string */
    private $alias;

    /** @var AppStore\AppInstance  */
    private $app;

    /** @var mixed  */
    private $object;

    /** @var string */
    private $aliasType;

    /**
     * @param ORM\EntityManager $entityManager
     */
    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string|ObjectAlias\QualifiedName $name
     * @return Builder
     * @throw \DomainException
     */
    public function setAlias($name)
    {
        $qualifiedName = null;
        if (is_string($name) ) {
            $qualifiedName = ObjectAlias\Converters::toNameFromString($name);
        } else if ($name instanceof ObjectAlias\QualifiedName) {
            $qualifiedName = $name;
        }

        if (is_null($qualifiedName)) {
            throw new \DomainException('invalid alias');
        }

        if (! ObjectAlias\QualifiedName::isValidIdentifier($qualifiedName)) {
            throw new \DomainException('invalid identifier');
        }

        $this->alias = is_string($name) ? $name : ObjectAlias\Converters::toStringFromName($qualifiedName);
        return $this;
    }

    /**
     * @param string|int|AppStore\App|FullyQualifiedAppName $ref
     * @return Builder
     * @throw \DomainException
     */
    public function setApp($ref)
    {
        if ($ref instanceof AppStore\AppInstance) {
            $this->app = $ref;
            return $this;
        }

        if ($ref instanceof FullyQualifiedAppName) {
            $id = $ref->getId();
        } else {
            $id = $ref;
        }

        $repository = $this->entityManager->getRepository(AppStore\AppInstance::class);
        $app = $repository->find($id);

        if ($app instanceof AppStore\AppInstance) {
            $this->app = $app;
            return $this;
        }

        throw new \DomainException(sprintf('can not find application with id %s', $id));
    }

    /**
     * @param mixed $object
     * @return Builder
     * @throw \DomainException
     */
    public function setObject($object)
    {
        $objectType = get_class($object);
        $aliasType = Aliases::resolveAliasType($objectType, $this->entityManager);

        if (is_string($aliasType)) {
            $this->aliasType = $aliasType;
            $this->object = $object;
            return $this;
        }

        throw new \DomainException(sprintf('alias can not be defined for object of type %s', $objectType));
    }

    /**
     * @return bool
     */
    public function canBuild()
    {
        return !empty($this->alias) && !empty($this->aliasType) && !empty($this->object);
    }

    /**
     * @return AbstractAlias
     */
    public function build()
    {
        if (! $this->canBuild()) {
            throw new \RuntimeException('builder is not ready');
        }

        $aliasFactory = new \ReflectionClass($this->aliasType);
        /** @var AbstractAlias $alias */
        $alias = $aliasFactory->newInstance();

        $alias->setAlias($this->alias);
        $alias->tryAndSetObject($this->object);

        if ($alias->getObject() !== $this->object) {
            throw new \RuntimeException('builder failed to set the aliased object');
        }

        if ($this->app) {
            $alias->setAppInstance($this->app);
        }

        return $alias;
    }
}
