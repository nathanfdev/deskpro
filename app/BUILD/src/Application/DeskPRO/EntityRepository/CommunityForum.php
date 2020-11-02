<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic as CommunityTopicEntity;
use Application\DeskPRO\Entity\CommunityTopicComment as CommunityTopicCommentEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\EntityRepository\Helper\CommentHelper;
use Application\DeskPRO\Searcher\CommunitySearch;
use Doctrine\ORM\Query;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * Class CommunityForum.
 *
 * @method array getFlatHierarchy()
 * @method array getInHierarchy()
 */
class CommunityForum extends AbstractCategoryRepository
{
    /** @var array|null */
    protected $all_cats = null;

    /**
     * @var \Application\DeskPRO\EntityRepository\Helper\CommentHelper
     */
    protected $_comment_helper = null;

    /**
     * @return \Application\DeskPRO\EntityRepository\Helper\CommentHelper
     */
    public function getCommentHelper()
    {
        if ($this->_comment_helper !== null) {
            return $this->_comment_helper;
        }

        $this->_comment_helper = new CommentHelper(
            $this->getEntityManager(),
            $this,
            $this->getEntityName(),
            $this->getClassMetadata(),
            'DeskPRO:CommunityTopicComment',
            'community_topic_comments',
            'topic_id'
        );

        return $this->_comment_helper;
    }

    public function getPermissionTableName()
    {
        return 'community_forum2usergroup';
    }

    public function getCategoryField()
    {
        return 'community_forum_id';
    }

    /**
     * Get an array of categories.
     *
     * @return array
     */
    public function getCategoryOptions()
    {
        if (!$this->all_cats === null) {
            return $this->all_cats;
        }

        $this->all_cats = App::getDb()->fetchAllKeyed('
            SELECT id, parent_id title
            FROM community_forums
            ORDER BY display_order DESC
        ', [], 'id');

        return $this->all_cats;
    }

    public function getFullHierarchy()
    {
        if ($this->hierarchy !== null) {
            return $this->hierarchy;
        }

        $this->hierarchy = Arrays::intoHierarchy($this->getCategoryOptions());

        return $this->hierarchy;
    }

    public function getBySlug($slug)
    {
        $id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
        if (!$id) {
            return;
        }

        return $this->find($id);
    }

    public function getAll()
    {
        return $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:CommunityForum f INDEX BY f.id
            ORDER BY f.display_order ASC
        ')->execute();
    }

    /**
     * @param int[] $forumIds
     *
     * @return array
     */
    public function getTopicCountPerForum(array $forumIds)
    {
        $counts = $this->createQueryBuilder('f')
            ->select('f.id AS forum_id, COUNT(t.id) AS topic_count')
            ->leftJoin(\Application\DeskPRO\Entity\CommunityTopic::class, 't', 'WITH', 'f.id = IDENTITY(t.forum)')
            ->andWhere('f.id IN (:forumIds)')
            ->andWhere('t.status != :status')->setParameter('status', 'hidden')
            ->setParameter('forumIds', $forumIds)
            ->groupBy('f')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY)
        ;

        return array_reduce($counts, function (array $all, array $row) {
            $all[(int) $row['forum_id']] = (int) $row['topic_count'];

            return $all;
        }, []);
    }

    /**
     * @param array $forumIds
     * @param int   $maxResults
     *
     * @return array
     */
    public function getLatestActivityPerForum(array $forumIds, $maxResults = 4)
    {
        $activity = [];

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('t.id AS topic_id', 'c.id as comment_id', 'CASE WHEN MAX(c.date_created) IS NOT NULL THEN MAX(c.date_created) ELSE t.date_created AS date_max')
            ->from(CommunityTopicEntity::class, 't')
            ->leftJoin('t.comments', 'c')
            ->where('IDENTITY(t.forum) = :forumId')
            ->andWhere('t.status != :topic_status')
            ->andWhere('c.status = :comment_status OR c.status IS NULL')
            ->setParameter('topic_status', CommunityTopicEntity::STATUS_HIDDEN)
            ->setParameter('comment_status', CommunityTopicCommentEntity::STATUS_VISIBLE)
            ->groupBy('t.id')
            ->orderBy('date_max', 'DESC')
            ->setMaxResults($maxResults)
        ;

        /** @var \Application\DeskPRO\Entity\CommunityForum $forum */
        foreach ($this->findBy(['id' => $forumIds]) as $forum) {
            $qb->setParameter('forumId', $forum->getId());
            $recentActivity = $qb->getQuery()->getResult();

            $topicIds   = [];
            $commentIds = [];

            foreach ($recentActivity as $value) {
                if ($value['comment_id']) {
                    $commentIds[$value['comment_id']] = $value['comment_id'];
                }
                if ($value['topic_id']) {
                    $topicIds[$value['topic_id']] = $value['topic_id'];
                }
            }

            $topics   = [];
            $comments = [];

            if ($topicIds) {
                $topicsQb = $em->createQueryBuilder();
                $topicsQb
                    ->select('t, p')
                    ->from(CommunityTopicEntity::class, 't')
                    ->join('t.person', 'p')
                    ->where('t.id IN (:topic_ids)')
                    ->setParameter('topic_ids', $topicIds)
                ;

                /** @var CommunityTopicEntity[] $result */
                $result = $topicsQb->getQuery()->getResult();
                foreach ($result as $topic) {
                    $topics[$topic->getId()] = $topic;
                }
            }
            if ($commentIds) {
                $commentsQb = $em->createQueryBuilder();
                $commentsQb
                    ->select('c, p')
                    ->from(CommunityTopicCommentEntity::class, 'c')
                    ->join('c.person', 'p')
                    ->where('c.id IN (:comment_ids)')
                    ->setParameter('comment_ids', $commentIds)
                ;

                /** @var CommunityTopicCommentEntity[] $result */
                $result = $commentsQb->getQuery()->getResult();
                foreach ($result as $comment) {
                    $comments[$comment->getId()] = $comment;
                }
            }

            $activity[$forum->getId()] = array_map(function ($data) use ($topics, $comments) {
                return [
                    'topic'   => isset($topics[$data['topic_id']]) ? $topics[$data['topic_id']] : null,
                    'comment' => $data['comment_id'] && isset($comments[$data['comment_id']]) ? $comments[$data['comment_id']] : null,
                ];
            }, $recentActivity);
        }

        return $activity;
    }

    /**
     * @param int  $id
     * @param bool $agent_only
     *
     * @return array
     */
    public function getUserGroups($id, $agent_only = false)
    {
        return
            $this->getEntityManager()
            ->createQuery(
                'SELECT u.id, u.title
                FROM DeskPRO:CommunityForum f
                JOIN f.usergroups u
                WHERE f.id = :id AND u.is_agent_group = :agent_only'
            )
            ->setParameter('id', $id)
            ->setParameter('agent_only', $agent_only)
            ->execute();
    }

    public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal')
    {
        $counts = [0 => ['popular' => 0, 'new' => 0, 'active' => 0, 'closed' => 0]];
        foreach ($this->children() as $c) {
            $cat_counts = [];

            $searcher = new CommunitySearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(CommunitySearch::TERM_FORUM, 'is', $c['id']);
            $searcher->addTerm(CommunitySearch::TERM_STATUS, 'is', CommunityTopicEntity::STATUS_NEW);
            $cat_counts['new'] = $searcher->getCount();

            $searcher = new CommunitySearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(CommunitySearch::TERM_FORUM, 'is', $c['id']);
            $searcher->addTerm(CommunitySearch::TERM_STATUS, 'is', CommunityTopicEntity::STATUS_ACTIVE);
            $cat_counts['active'] = $searcher->getCount();

            $searcher = new CommunitySearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(CommunitySearch::TERM_FORUM, 'is', $c['id']);
            $searcher->addTerm(CommunitySearch::TERM_STATUS, 'is', CommunityTopicEntity::STATUS_CLOSED);
            $cat_counts['closed'] = $searcher->getCount();

            $cat_counts['all'] = array_sum($cat_counts);

            $counts[$c['id']] = $cat_counts;

            // 0 is sum of all root nodes
            if (!$c['depth']) {
                $counts[0]['new'] += $counts[$c['id']]['new'];
                $counts[0]['active'] += $counts[$c['id']]['active'];
                $counts[0]['closed'] += $counts[$c['id']]['closed'];
            }
        }

        $counts[0]['all'] = array_sum($counts[0]);

        return $counts;
    }
}
