<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Feedback as FeedbackEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\EntityRepository\Helper\CommentHelper;
use Application\DeskPRO\Searcher\FeedbackSearch;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * Class FeedbackCategory.
 *
 * @method array getFlatHierarchy()
 * @method array getInHierarchy()
 */
class FeedbackCategory extends AbstractCategoryRepository
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
            'DeskPRO:FeedbackComment',
            'feedback_comments',
            'feedback_id'
        );

        return $this->_comment_helper;
    }

    public function getPermissionTableName()
    {
        return 'feedback_category2usergroup';
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
            FROM feedback_categories
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
            SELECT c
            FROM DeskPRO:FeedbackCategory c INDEX BY c.id
            ORDER BY c.display_order ASC
        ')->execute();
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
                FROM DeskPRO:FeedbackCategory c
                JOIN c.usergroups u
                WHERE c.id = :id AND u.is_agent_group = :agent_only'
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

            $searcher = new FeedbackSearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(FeedbackSearch::TERM_CATEGORY, 'is', $c['id']);
            $searcher->addTerm(FeedbackSearch::TERM_STATUS, 'is', FeedbackEntity::STATUS_NEW);
            $cat_counts['new'] = $searcher->getCount();

            $searcher = new FeedbackSearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(FeedbackSearch::TERM_CATEGORY, 'is', $c['id']);
            $searcher->addTerm(FeedbackSearch::TERM_STATUS, 'is', FeedbackEntity::STATUS_ACTIVE);
            $cat_counts['active'] = $searcher->getCount();

            $searcher = new FeedbackSearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(FeedbackSearch::TERM_CATEGORY, 'is', $c['id']);
            $searcher->addTerm(FeedbackSearch::TERM_STATUS, 'is', FeedbackEntity::STATUS_CLOSED);
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
