<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\DBAL\Connection;
use DeskPRO\Component\Util\TypeUtils;

class CustomDefAbstract extends AbstractEntityRepository
{
    private $didLoadHierarchy = false;

    public static function getCacheId($id)
    {
        $str = 'customdef'.md5(get_called_class()).'_'.$id;

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if (TypeUtils::isIntLike($id)) {
            return parent::find($id, $lockMode, $lockVersion);
        } elseif ($this->_class->hasAssociation('aliases')) {
            return $this->findOneByAlias($id);
        } else {
            return null;
        }
    }

    /**
     * @param string $alias
     *
     * @return \Application\DeskPRO\Entity\CustomDefAbstract|null
     */
    public function findOneByAlias($alias)
    {
        return $this->_em->createQuery("
            SELECT f, a
            FROM {$this->_entityName} f
            JOIN f.aliases a
            WHERE a.alias = :alias
        ")
            ->setParameter('alias', $alias)
            ->setMaxResults(1)
            ->getOneOrNullResult();
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
        $entity = str_replace('\\CustomDef', '\\CustomData', $this->getEntityName());
        $table  = $this->_em->getRepository($entity)->getTableName();
        $con    = $this->_em->getConnection();
        $q      = sprintf('update %s set field_id = :to where field_id in (:ids)', $table);
        $con->executeQuery(
            $q,
            ['ids' => $fromIds, 'to' => $toId],
            ['ids' => Connection::PARAM_INT_ARRAY, 'to' => \PDO::PARAM_INT]
        );
    }
}
