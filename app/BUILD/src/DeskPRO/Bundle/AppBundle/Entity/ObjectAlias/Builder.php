<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
        return !empty($this->name) && !empty($this->aliasType) && !empty($this->object);
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
