<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ArticleCategory as CategoryEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\EntityRepository\Helper\CommentHelper;
use Application\DeskPRO\Searcher\ArticleSearch;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class ArticleCategory extends AbstractCategoryRepository
{
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
            'DeskPRO:ArticleComment',
            'article_comments',
            'article_id'
        );

        return $this->_comment_helper;
    }

    public function getPermissionTableName()
    {
        return 'article_category2usergroup';
    }

    public function getCategoriesById(array $ids)
    {
        $ids = Arrays::removeFalsey($ids);

        if (!$ids) {
            return [];
        }

        $ids = implode(',', $ids);

        return $this->getEntityManager()->createQuery("
            SELECT c
            FROM DeskPRO:ArticleCategory c
            WHERE c.id IN ($ids)
            ORDER BY c.display_order
        ")->execute();
    }

    public function getCategoryOptions()
    {
        if ($this->all_cats !== null) {
            return $this->all_cats;
        }

        $this->all_cats = App::getDb()->fetchAllKeyed('
            SELECT id, parent_id, title
            FROM article_categories
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

    public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal', $from_parent = 0)
    {
        $counts = ['0' => 0, '0_total' => 0];

        foreach ($this->getIds() as $cid) {
            $searcher = new ArticleSearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(ArticleSearch::TERM_CATEGORY_SPECIFIC, 'is', $cid);
            $searcher->addTerm(ArticleSearch::TERM_AGENT_LIST, 'is', 1);
            $counts[$cid] = $searcher->getCount();
        }

        $counts = $this->getTotalCounts($counts);

        // Need to do a total count separately because articles exist in multiple cats,
        // we cant use that to do a tally
        $counts['0'] = 0;

        $searcher = new ArticleSearch();
        $searcher->setPersonContext($person_context);
        $searcher->addTerm(ArticleSearch::TERM_AGENT_LIST, 'is', 1);
        $counts['0_total'] = $searcher->getCount();

        return $counts;
    }

    public function gatherOrderedBreadcrumbTree(CategoryEntity $category)
    {
        // TODO: we should make this more of a query, rather than this which does several requests
        return $category->getTreeParents();
    }
}
