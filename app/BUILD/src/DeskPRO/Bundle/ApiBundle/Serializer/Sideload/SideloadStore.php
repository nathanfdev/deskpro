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

namespace DeskPRO\Bundle\ApiBundle\Serializer\Sideload;

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

    protected $loaded;

    /**
     * @param $entity
     */
    public function addSideload($entity)
    {
        /* @var EntityInterface|DomainObject $entity */
        $fqcn  = get_class($entity);
        $snake = TypeUtils::getSnakeCaseBaseTypeName($entity);
        if (!isset($this->sideloads[$fqcn])) {
            $this->sideloads[$fqcn] = [];
        }

        $this->sideloads[$fqcn][] = $entity->getId();
        $this->classmap[$snake]   = $fqcn;
    }

    public function getFqcn($snake)
    {
        return $this->classmap[$snake];
    }

    public function getSideloads($fqcn)
    {
        if (!isset($this->loaded[$fqcn])) {
            $this->loaded[$fqcn] = [];
        }

        $ids_to_load = array_diff($this->sideloads[$fqcn], $this->loaded[$fqcn]);

        $this->loaded[$fqcn] = array_merge($this->loaded[$fqcn], $ids_to_load);

        return $ids_to_load;
    }
}
