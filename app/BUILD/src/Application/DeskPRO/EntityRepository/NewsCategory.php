<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\EntityRepository\Helper\CommentHelper;
use Application\DeskPRO\Searcher\NewsSearch;
use Orb\Util\Strings;

class NewsCategory extends AbstractCategoryRepository
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
            'DeskPRO:NewsComment',
            'news_comments',
            'news_id'
        );

        return $this->_comment_helper;
    }

    public function getPermissionTableName()
    {
        return 'news_category2usergroup';
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
            SELECT id, title
            FROM news_categories
            ORDER BY id DESC
        ', [], 'id');

        return $this->all_cats;
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
            FROM DeskPRO:NewsCategory c INDEX BY c.id
            ORDER BY c.id DESC
        ')->execute();
    }

    public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal')
    {
        $counts = ['0' => 0, '0_total' => 0];

        foreach ($this->children() as $c) {
            $searcher = new NewsSearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(NewsSearch::TERM_CATEGORY_SPECIFIC, 'is', $c['id']);
            $searcher->addTerm(NewsSearch::TERM_AGENT_LIST, 'is', 'published');

            $counts[$c['id']] = $searcher->getCount();

            $counts['0_total'] += $counts[$c['id']];
        }

        $repos    = $this;
        $fn_count = function ($node) use (&$counts, $repos, &$fn_count) {
            $total = 0;
            foreach ($repos->children($node, true) as $c) {
                // We already have the single count
                $total += $counts[$c['id']];

                // Now add up all its subs
                $total += $fn_count($c);
            }

            if ($node) {
                $counts[$node['id'].'_total'] = $total;
            }

            return $total;
        };

        $fn_count(null);

        return $counts;
    }
}
