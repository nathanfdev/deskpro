<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\TmpData as TmpDataEntity;

class TmpData extends AbstractEntityRepository
{
    /**
     * @param      $code
     * @param null $type
     *
     * @return null|\Application\DeskPRO\Entity\TmpData
     */
    public function getByCode($code, $type = null)
    {
        $info = TmpDataEntity::getPartsFromCode($code);
        if (!$info || empty($info['id']) || !$info['id']) {
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
     * @return TmpDataEntity
     */
    public function getByName($name, $expired = null)
    {
        $q      = 'select t from DeskPRO:TmpData t where t.name = :name ';
        $params = ['name' => $name];

        if (true === $expired) {
            $q .= 'and t.date_expire <= :date';
            $params['date'] = new \DateTime();
        }

        if (false === $expired) {
            $q .= 'and t.date_expire > :date';
            $params['date'] = new \DateTime();
        }

        return $this->getEntityManager()->createQuery($q)->setParameters($params)->getResult();
    }

    /**
     * @param TmpDataEntity $data
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeDupes(TmpDataEntity $data)
    {
        if (!$data['name']) {
            return;
        }
        $this->getEntityManager()->getConnection()->executeQuery(
            sprintf('delete from %s where name = :name and id != :id', $this->getTableName()),
            ['name' => $data['name'], 'id' => $data['id']],
            [\PDO::PARAM_STR, \PDO::PARAM_INT]
        );
    }

    /**
     * @param $name
     * @param $time
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    public function getCountByName($name, $time)
    {
        $time = time() - (int) $time;

        return (int) $this->getEntityManager()->getConnection()->executeQuery(
            sprintf('select count(*) from %s where name = :name and date_created > :date', $this->getTableName()),
            ['name' => $name, 'date' => date('Y-m-d H:i:s', $time)]
        )->fetchColumn();
    }
}
