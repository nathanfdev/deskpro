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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\DBAL\Connection;

class CustomDefAbstract extends AbstractEntityRepository
{
    private $didLoadHierarchy = false;

    public static function getCacheId($id)
    {
        $str = 'customdef'.md5(get_called_class()).'_'.$id;

        return $str;
    }

    /**
     * After loading a full collection using one of the getters below, we
     * do a separate query to select just the hierarchy. This prevents query in a loop whenever ->children is called.
     *
     * You could also add a LEFT JOIN f.children to any of the getters below,
     * but doing that results in duplicate data for the
     * parent in each child row, which could potentially be a lot of actual data (titles+options+descriptions etc)
     * Here we're just getting partials, so a very small result set, even if there are dupe rows.
     *
     * So the idea is this is a bit defensive and that two small queries is better than 1 query with potentially
     * a very large result set.
     */
    private function preloadHierarchy()
    {
        if ($this->didLoadHierarchy) {
            return;
        }
        $this->didLoadHierarchy = true;
        $this->_em->createQuery("
            SELECT f, ch
            FROM {$this->_entityName} f INDEX BY f.id
            JOIN f.children ch
            ORDER BY f.display_order ASC, f.title
        ")->execute();
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $q = $this->_em->createQuery("
            SELECT f
            FROM {$this->_entityName} f INDEX BY f.id
            ORDER BY f.display_order ASC, f.title
        ");

        $res = $q->execute();
        if (count($res)) {
            $this->preloadHierarchy();
        }

        return $res;
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefAbstract[]
     */
    public function getEnabledFields()
    {
        $q = $this->_em->createQuery("
            SELECT f
            FROM {$this->_entityName} f INDEX BY f.id
            WHERE f.is_enabled = true
            ORDER BY f.display_order ASC, f.title
        ");

        $res = $q->execute();
        if (count($res)) {
            $this->preloadHierarchy();
        }

        return $res;
    }

    public function getEnabledUserFields()
    {
        $q = $this->_em->createQuery("
            SELECT f
            FROM {$this->_entityName} f INDEX BY f.id
            WHERE f.is_enabled = true AND f.is_agent_field = false
            ORDER BY f.display_order ASC, f.title
        ");

        $res = $q->execute();
        if (count($res)) {
            $this->preloadHierarchy();
        }

        return $res;
    }

    /**
     * @return array
     */
    public function getTopFields()
    {
        $q = $this->_em->createQuery("
            SELECT f
            FROM {$this->_entityName} f INDEX BY f.id
            WHERE f.parent IS NULL
            ORDER BY f.display_order ASC, f.title
        ");

        $res = $q->execute();
        if (count($res)) {
            $this->preloadHierarchy();
        }

        return $res;
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefAbstract[]
     */
    public function getEnabledTopFields()
    {
        $q = $this->_em->createQuery("
            SELECT f
            FROM {$this->_entityName} f INDEX BY f.id
            WHERE f.parent IS NULL AND f.is_enabled = true
            ORDER BY f.display_order ASC, f.title
        ");

        $res = $q->execute();
        $this->preloadHierarchy();

        return $res;
    }

    /**
     * @param array $display_orders
     */
    public function updateDisplayOrders(array $display_orders)
    {
        $display_orders = array_values($display_orders);

        $db = $this->_em->getConnection();
        $db->beginTransaction();

        $x = 0;
        foreach ($display_orders as $tr_id) {
            $x += 10;
            $db->update($this->getTableName(), ['display_order' => $x], ['id' => $tr_id]);
        }

        $db->commit();
    }

    /**
     * @param array $ids
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return bool
     */
    public function hasData(array $ids)
    {
        $entity = str_replace('\\CustomDef', '\\CustomData', $this->getEntityName());
        $table  = $this->_em->getRepository($entity)->getTableName();
        $con    = $this->_em->getConnection();
        $q      = sprintf('select count(*) from %s where field_id in (:ids)', $table);
        $res    = $con->executeQuery($q, ['ids' => $ids], ['ids' => Connection::PARAM_INT_ARRAY])->fetchColumn();

        return (bool) $res;
    }

    /**
     * @param array $ids
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return mixed
     */
    public function getByOptions(array $ids)
    {
        return $this->getEntityManager()->createQuery(
            "
            SELECT f
            FROM {$this->_entityName} f INDEX BY f.id
            JOIN f.children c
            WHERE c.id in (:ids)
        "
        )->setParameter('ids', $ids)->getOneOrNullResult();
    }

    public function delete(array $ids)
    {
        return $this->getEntityManager()->createQuery(
            "DELETE {$this->_entityName} f WHERE f.id IN (:ids)"
        )->setParameter('ids', $ids)->execute();
    }

    public function updateTo(array $fromIds, $toId)
    {
        $table = str_replace('_def_', '_data_', $this->getTableName());
        $con   = $this->_em->getConnection();
        $q     = sprintf('update %s set field_id = :to where field_id in (:ids)', $table);
        $con->executeQuery(
            $q,
            ['ids' => $fromIds, 'to' => $toId],
            ['ids' => Connection::PARAM_INT_ARRAY, 'to' => \PDO::PARAM_INT]
        );
    }
}
