<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;

class CommentAbstract extends AbstractEntityRepository
{
    const FIELD = '';

    public function getByIds(array $ids, $keep_order = false)
    {
        if (!$ids) {
            return [];
        }

        $ids = implode(',', $ids);

        return $this->getEntityManager()->createQuery('
            SELECT c
            FROM '.$this->_entityName." c INDEX BY c.id
            WHERE c.id IN ($ids)
        ")->execute();
    }

    public function getComments($object, $show_validating = true)
    {
        if ($show_validating) {
            return $this->getEntityManager()->createQuery('
                SELECT c
                FROM '.$this->_entityName.' c
                WHERE c.status != ?1 AND c.'.static::FIELD.' = ?2
                ORDER BY c.id DESC
            ')->setParameter(1, 'deleted')->setParameter(2, $object)->execute();
        } else {
            return $this->getEntityManager()->createQuery('
                SELECT c
                FROM '.$this->_entityName.' c
                WHERE c.status = ?1 AND c.'.static::FIELD.' = ?2
                ORDER BY c.id DESC
            ')->setParameter(1, 'visible')->setParameter(2, $object)->execute();
        }
    }

    public function getDisplayComments($object, PersonEntity $person_context = null, $visitor_id = 0)
    {
        $params = ['obj_id' => $object->getId()];
        $dql    = "SELECT c FROM {$this->_entityName} c WHERE c.".static::FIELD." = :obj_id AND (c.status = 'visible'";
        if ($person_context && $person_context->getId()) {
            $dql .= ' OR c.person = :person_id';
            $params['person_id'] = $person_context->getId();
        }
        if ($visitor_id) {
            $dql .= ' OR c.visitor = :visitor_id';
            $params['visitor_id'] = $visitor_id;
        }
        $dql .= ')';

        return $this->_em->createQuery($dql)->execute($params);
    }

    public function countAwaitingValidation()
    {
        $table = $this->getClassMetadata()->getTableName();

        return App::getDb()->fetchColumn("
            SELECT COUNT(*)
            FROM $table
            WHERE is_reviewed = 0
        ");
    }

    public function getValidatingComments()
    {
        return $this->getEntityManager()->createQuery('
            SELECT c
            FROM '.$this->_entityName.' c
            LEFT JOIN c.person p
            WHERE c.is_reviewed = ?1
            ORDER BY c.id DESC
        ')->setParameter(1, false)
          ->execute();
    }

    /**
     * @param $content
     * @param $person
     * @param null $name
     * @param null $email
     */
    public function getDuplicate($content, $person = null, $name = null, $email = null)
    {
        $qb = $this->createQueryBuilder('c');

        if ($person && $person->getId()) {
            $qb->andWhere('c.person = :person');
            $qb->setParameter('person', $person);
        } else {
            if ($name) {
                $qb->andWhere('c.name = :name');
                $qb->setParameter('name', $name);
            }
            if ($email) {
                $qb->andWhere('c.email = :email');
                $qb->setParameter('email', $email);
            }
        }

        $qb->setMaxResults(5);
        $qb->orderBy('c.id', 'DESC');

        $comments = $qb->getQuery()->execute();

        foreach ($comments as $comment) {
            if ($comment->getContentReal() == $content) {
                return $comment;
            }
        }

        return;
    }
}
