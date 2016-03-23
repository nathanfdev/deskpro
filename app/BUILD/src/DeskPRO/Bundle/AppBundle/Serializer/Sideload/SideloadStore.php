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
use DeskPRO\Component\Util\TypeUtils;

/**
 * Class SideloadStore.
 */
class SideloadStore
{
    /**
     * @var array
     */
    protected $sideloads;

    /**
     * @var array
     */
    protected $classmap;

    /**
     * @var
     */
    protected $loaded;

    /**
     * @var
     */
    protected $not_loaded;

    /**
     * @param $entity
     */
    public function addSideload($entity)
    {
        $this->checkObject($entity);

        /* @var EntityInterface|DomainObject $entity */
        $fqcn  = get_class($entity);
        $snake = TypeUtils::getSnakeCaseBaseTypeName($entity);
        if (!isset($this->sideloads[$fqcn])) {
            $this->sideloads[$fqcn] = [];
        }

        $this->sideloads[$fqcn][] = $entity->getId();
        $this->classmap[$snake]   = $fqcn;
    }

    /**
     * @param array $interests
     */
    public function setInterests(array $interests = [])
    {
        foreach ($interests as $interest) {
            $fqcn = $this->getFqcn($interest);
            if ($fqcn) {
                $this->not_loaded[$fqcn] = true;
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
        return $this->classmap[$snake];
    }

    /**
     * @param $fqcn
     *
     * @return array
     */
    public function getSideloads($fqcn)
    {
        $this->not_loaded[$fqcn] = false;
        if (!isset($this->loaded[$fqcn])) {
            $this->loaded[$fqcn] = [];
        }

        $ids_to_load = array_diff($this->sideloads[$fqcn], $this->loaded[$fqcn]);

        $this->loaded[$fqcn] = array_merge($this->loaded[$fqcn], $ids_to_load);

        return $ids_to_load;
    }

    /**
     * @return mixed
     */
    public function hasSideloads()
    {
        return array_reduce($this->not_loaded, [$this, 'reduce'], false);
    }

    /**
     * @param $carry
     * @param $item
     *
     * @return bool
     */
    protected function reduce($carry, $item)
    {
        return $carry || $item;
    }

    /**
     * @param $entity
     */
    protected function checkObject($entity)
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
}
