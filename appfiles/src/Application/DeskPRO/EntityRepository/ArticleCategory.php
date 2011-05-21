<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\ORM\EntityRepository\NestedTreeRepository;
use Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;

use Orb\Util\Arrays;
use Orb\Util\Strings;

class ArticleCategory extends AbstractNestedTreeCategoryRepository
{
	protected $_agent_cat_helper;
	protected $_user_cat_helper;

	/**
	 * @return \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy
	 */
	public function getAgentCategoryHelper()
	{
		if ($this->_agent_cat_helper !== null) return $this->_agent_cat_helper;

		$this->_agent_cat_helper = new CategoryHierarchy($this, $this->getClassMetadata());
		$this->_agent_cat_helper->setWhereCond("is_agent = 1");

		return $this->_agent_cat_helper;
	}

	/**
	 * @return \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy
	 */
	public function getUserCategoryHelper()
	{
		if ($this->_user_cat_helper !== null) return $this->_agent_cat_helper;

		$this->_user_cat_helper = new CategoryHierarchy($this, $this->getClassMetadata());
		$this->_user_cat_helper->setWhereCond("is_agent = 0");

		return $this->_user_cat_helper;
	}


	
	public function getCategoriesById(array $ids)
	{
		$ids = Arrays::removeFalsey($ids);

		if (!$ids) return array();

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
		if ($this->all_cats !== null) return $this->all_cats;

		$this->all_cats = App::getDb()->fetchAllKeyed("
			SELECT id, parent_id, title
			FROM article_categories
			ORDER BY display_order DESC
		", array(), 'id');

		return $this->all_cats;
	}

	public function getFullHierarchy()
	{
		if ($this->hierarchy !== null) return $this->hierarchy;

		$this->hierarchy = Arrays::intoHierarchy($this->getCategoryOptions());

		return $this->hierarchy;
	}


	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}
}