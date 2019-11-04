<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic as CommunityTopicEntity;
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
     * @param int   $numberOfComments
     *
     * @return array
     */
    public function getLatestCommentsPerForum(array $forumIds, $numberOfComments = 4)
    {
        $latestCommentsByForum = [];
        $latestCommentsQuery   = $this->getEntityManager()->createQueryBuilder()
            ->select('c, p, t')
            ->from(\Application\DeskPRO\Entity\CommunityTopicComment::class, 'c')
            ->innerJoin('c.person', 'p')
            ->innerJoin('c.topic', 't')
            ->andWhere('c.status != \'hidden\'')
            ->andWhere('IDENTITY(t.forum) = :forumId')
            ->andWhere('t.status != :hiddenStatus')
            ->orderBy('c.date_created', 'DESC')
            ->setParameter('hiddenStatus', \Application\DeskPRO\Entity\CommunityTopic::STATUS_HIDDEN)
            ->setMaxResults($numberOfComments)
        ;

        /** @var \Application\DeskPRO\Entity\CommunityForum $forum */
        foreach ($this->findBy(['id' => $forumIds]) as $forum) {
            $latestCommentsByForum[$forum->getId()] = $latestCommentsQuery
                ->setParameter('forumId', $forum->getId())
                ->getQuery()
                ->getResult()
            ;
        }

        return $latestCommentsByForum;
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
