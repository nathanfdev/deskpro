<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity;

/**
 * Class DataStore.
 */
class DataStore extends AbstractEntityRepository
{
    /**
     * Get data by code.
     *
     * @param string $code
     * @param string $type
     *
     * @return null|object
     */
    public function getByCode($code, $type = null)
    {
        $info = Entity\DataStore::getPartsFromCode($code);
        if (!$info) {
            return;
        }

        $tmp_data = $this->find($info['id']);
        if ($tmp_data['auth'] != $info['auth']) {
            return;
        }
        if ($type and $tmp_data->getType() != $type) {
            return;
        }

        return $tmp_data;
    }

    /**
     * Get data by its unique name.
     *
     * @param string $name
     * @param bool   $create_unset
     *
     * @return Entity\DataStore
     */
    public function getByName($name, $create_unset = false)
    {
        $ds = $this->findOneBy(array('name' => $name));

        if (!$ds && $create_unset) {
            $ds       = new Entity\DataStore();
            $ds->name = $name;
        }

        return $ds;
    }

    /**
     * Get data by prefix.
     *
     * @param $prefix
     *
     * @return Entity\DataStore[]
     */
    public function getByPrefix($prefix)
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT d FROM DeskPRO:DataStore d WHERE d.name LIKE :prefix')
            ->setParameter('prefix', $prefix.'%')
            ->getResult()
        ;
    }
}
