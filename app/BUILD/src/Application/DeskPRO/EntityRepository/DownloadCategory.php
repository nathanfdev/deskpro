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
use Application\DeskPRO\Searcher\DownloadSearch;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class DownloadCategory extends AbstractCategoryRepository
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
            'DeskPRO:DownlaodComment',
            'download_comments',
            'download_id'
        );

        return $this->_comment_helper;
    }

    public function getPermissionTableName()
    {
        return 'download_category2usergroup';
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
            FROM download_categories
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

    public function getAllCounts(PersonEntity $person_context = null, $cache_name = 'portal')
    {
        $counts = ['0' => 0, '0_total' => 0];

        foreach ($this->getIds() as $cid) {
            $searcher = new DownloadSearch();
            $searcher->setPersonContext($person_context);
            $searcher->addTerm(DownloadSearch::TERM_CATEGORY_SPECIFIC, 'is', $cid);
            $searcher->addTerm(DownloadSearch::TERM_AGENT_LIST, 'is', 'published');

            $counts[$cid] = $searcher->getCount();
        }

        $counts = $this->getTotalCounts($counts);

        return $counts;
    }
}
