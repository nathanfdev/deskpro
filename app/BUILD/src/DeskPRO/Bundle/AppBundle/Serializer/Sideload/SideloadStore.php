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

namespace DeskPRO\Bundle\AppBundle\Serializer\Sideload;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\Common\Proxy\Proxy;

/**
 * Class SideloadStore.
 */
class SideloadStore
{
    /**
     * @var array
     */
    private $sideloads;

    /**
     * @var array
     */
    private $classmap;

    /**
     * @var
     */
    private $loaded;

    /**
     * @var
     */
    private $notLoaded = [];

    /**
     * @var array
     */
    private $customs = [];

    /**
     * @var array
     */
    private $interests = [];

    /**
     * @var bool
     */
    private $interestsWasSet = false;

    /**
     * @param $entity
     */
    public function addSideload($entity)
    {
        $this->checkObject($entity);

        /* @var EntityInterface|DomainObject $entity */
        $fqcn = get_class($entity);

        if ($entity instanceof Proxy) {
            $reflection = new \ReflectionClass($entity);
            $parent     = $reflection->getParentClass();
            $fqcn       = $parent->getName();
        }

        $this->addSideloadString($fqcn, $entity->getId());
    }

    public function addSideloadString($class, $id)
    {
        $snake = TypeUtils::getSnakeCaseBaseTypeName($class);
        if (!isset($this->sideloads[$snake])) {
            $this->sideloads[$snake] = [];
        }

        if ($this->interestsWasSet) {
            $this->updateNotLoaded($snake);
        }

        $this->sideloads[$snake][$id] = $id;
        $this->classmap[$snake]       = $class;
    }

    /**
     * @param string                   $interest
     * @param int                      $id
     * @param CallbackDeferredProperty $deferred
     */
    public function addCustomSideload($interest, $id, CallbackDeferredProperty $deferred)
    {
        if (!isset($this->customs[$interest])) {
            $this->customs[$interest] = [];
        }

        $this->updateNotLoaded($interest);

        $this->customs[$interest][$id] = new CustomSideload($id, $deferred);
    }

    /**
     * @param array $interests
     */
    public function setInterests(array $interests = [])
    {
        $this->interestsWasSet = true;
        $this->interests       = $interests;

        foreach ($this->interests as $interest) {
            $fqcn = $this->getFqcn($interest);
            if ($fqcn || array_key_exists($interest, $this->customs)) {
                $this->updateNotLoaded($interest);
            }
        }
    }

    /**
     * @param $snake
     *
     * @return mixed
     */
    public function getFqcn($snake)
    {
        return isset($this->classmap[$snake]) ? $this->classmap[$snake] : null;
    }

    /**
     * @param string $interest
     *
     * @return array
     */
    public function getSideloads($interest)
    {
        $this->updateNotLoaded($interest, false);

        if (!isset($this->loaded[$interest])) {
            $this->loaded[$interest] = [];
        }

        $ids_to_load = array_diff($this->sideloads[$interest], $this->loaded[$interest]);

        $this->loaded[$interest] += $ids_to_load;

        return $ids_to_load;
    }

    /**
     * @param $interest
     *
     * @return CustomSideload[]
     */
    public function getCustom($interest)
    {
        $this->updateNotLoaded($interest, false);

        if (!isset($this->loaded[$interest])) {
            $this->loaded[$interest] = [];
        }
        $return = [];
        foreach ($this->customs[$interest] as $custom) {
            if (false === array_search((int) $custom->getId(), $this->loaded[$interest])) {
                $this->loaded[$interest][] = (int) $custom->getId();
                $return[]                  = $custom;
            }
        }

        return $return;
    }

    /**
     * @return mixed
     */
    public function hasSideloads()
    {
        $hasSideloads = array_reduce($this->notLoaded, [$this, 'reduce'], false);

        return $hasSideloads;
    }

    /**
     * @param $interest
     *
     * @return bool
     */
    public function hasCustom($interest)
    {
        return isset($this->customs[$interest]);
    }

    /**
     * @param $carry
     * @param $item
     *
     * @return bool
     */
    private function reduce($carry, $item)
    {
        return $carry || $item;
    }

    /**
     * @param $entity
     */
    private function checkObject($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'SideloadStore::addSideload expected $entity to be and object, but [ %s ] given',
                    TypeUtils::getVarType($entity)
                )
            );
        }

        if (!$entity instanceof EntityInterface && !$entity instanceof DomainObject) {
            throw new \InvalidArgumentException(
                sprintf(
                    'SideloadStore::addSideload expected $entity to be an instance of EntityInterface or DomainObject, but [ %s ] given',
                    TypeUtils::getBaseTypeName($entity)
                )
            );
        }
    }

    /**
     * @param      $interest
     * @param bool $notLoaded
     */
    private function updateNotLoaded($interest, $notLoaded = true)
    {
        $snake = TypeUtils::getSnakeCaseBaseTypeName($interest);
        if (in_array($snake, $this->interests)) {
            $this->notLoaded[$snake] = $notLoaded;
        }
    }
}
