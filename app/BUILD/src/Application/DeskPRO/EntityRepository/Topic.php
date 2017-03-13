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

namespace Application\DeskPRO\EntityRepository;

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
     * @param bool       $reset
     * @param Guide|null $guide
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
            $select = 'id, parent_id, title, slug';

            $qb = $this->_em->getConnection()->createQueryBuilder();
            $qb->select($select);
            $qb->from($this->tableName);
            $qb->orderBy('display_order', 'ASC');
            $qb->addOrderBy('id', 'ASC');

            $params = [];
            if ($guide) {
                $qb->where('guide_id = ?');
                $params[] = $guide->getId();
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

        $topics                   = Arrays::intoHierarchy($topics, null);
        static::addParentSlug($topics);
        $this->topicHierarchy     = $topics;

        return $this->topicHierarchy;
    }

    protected static function addParentSlug(&$topics, $parentSlug = '') {
        foreach ($topics as &$topic) {
            $topic['parents_slug'] = $parentSlug;
            static::addParentSlug($topic['children'], $parentSlug . '/' . $topic['slug']);
        }
    }
}
