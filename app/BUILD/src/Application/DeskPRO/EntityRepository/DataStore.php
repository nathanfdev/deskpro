<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\DataStore as DataStoreEntity;

class DataStore extends AbstractEntityRepository
{
    public function getByCode($code, $type = null)
    {
        $info = DataStoreEntity::getPartsFromCode($code);
        if (!$info) {
            return;
        }

        $tmpdata = $this->find($info['id']);
        if ($tmpdata['auth'] != $info['auth']) {
            return;
        }

        if ($type and $tmpdata->getType() != $type) {
            return;
        }

        return $tmpdata;
    }

    /**
     * Get data by its unique name.
     *
     * @param string $name
     *
     * @return DataStoreEntity
     */
    public function getByName($name, $create_unset = false)
    {
        $ds = $this->findOneBy(['name' => $name]);

        if (!$ds && $create_unset) {
            $ds       = new DataStoreEntity();
            $ds->name = $name;
        }

        return $ds;
    }

    /**
     * Get data by prefix.
     *
     * @param $prefix
     *
     * @return array
     */
    public function getByPrefix($prefix)
    {
        return $this->getEntityManager()->createQuery('SELECT d FROM DeskPRO:DataStore d WHERE d.name LIKE :prefix')
            ->setParameter('prefix', $prefix.'%')->getResult();
    }
}
