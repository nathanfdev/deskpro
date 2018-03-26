<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Topic as TopicEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Util\Arrays;

class Topic extends AbstractEntityRepository
{
    /**
     * @var null|array
     */
    protected $topicHierarchy = null;

    /**
     * @var string
     */
    protected $tableName;

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->tableName = $class->getTableName();
    }

    /**
     * Get a plain hierarchy array.
     *
     * @param bool           $reset
     * @param Guide|int|null $guide
     *
     * @return array|null
     */
    public function getInHierarchy($reset = false, $guide = null)
    {
        if (!$reset && $this->topicHierarchy !== null) {
            return $this->topicHierarchy;
        }

        if (is_array($reset)) {
            $topics = $reset;
        } else {
            $select = 'id, parent_id, title, slug, display_order, no_content';

            $params = [];

            $qb = $this->_em->getConnection()->createQueryBuilder();
            $qb->select($select);
            $qb->from($this->tableName);
            $qb->where('status <> ?');
            $params[] = TopicEntity::STATUS_HIDDEN;
            $qb->orderBy('display_order', 'ASC');

            if ($guide) {
                if (is_object($guide)) {
                    $guideId = $guide->getId();
                } else {
                    $guideId = $guide;
                }

                $qb->andWhere('guide_id = ?');
                $params[] = $guideId;
            }

            $topics = $this->_em->getConnection()->fetchAllKeyed($qb->getSQL(), $params, 'id');
        }

        foreach ($topics as &$c) {
            foreach (['id', 'parent_id', 'brand_id'] as $k) {
                if (!empty($c[$k])) {
                    $c[$k] = (int) $c[$k];
                }
            }

            if (!isset($c['user_title']) || !$c['user_title']) {
                $c['user_title'] = $c['title'];
            }
        }
        unset($c);

        $topics = Arrays::intoHierarchy($topics, null);
        static::addParentSlug($topics);
        $this->topicHierarchy = $topics;

        return $this->topicHierarchy;
    }

    protected static function addParentSlug(&$topics, $parentSlug = '')
    {
        foreach ($topics as &$topic) {
            $topic['parents_slug'] = $parentSlug;
            static::addParentSlug($topic['children'], $parentSlug.'/'.$topic['slug']);
        }
    }
}
