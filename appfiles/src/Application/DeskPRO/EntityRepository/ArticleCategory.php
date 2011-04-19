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

use \Application\DeskPRO\App;
use \Application\DeskPRO\ORM\EntityRepository\NestedTreeRepository;

use Doctrine\ORM\Query,
    Gedmo\Tree\Strategy,
    Gedmo\Tree\Strategy\ORM\Nested,
    Gedmo\Exception\InvalidArgumentException,
    Doctrine\ORM\Proxy\Proxy;

use \Orb\Util\Arrays;
use \Orb\Util\Strings;

class ArticleCategory extends NestedTreeRepository
{
	protected $_hierarchy = array();

	public function getFullHierarchy()
	{
		if ($this->_hierarchy !== null) return $this->_hierarchy;

		$this->_hierarchy = App::getDb()->fetchAll("
			SELECT id, parent_id, title
			FROM article_categories
			ORDER BY id DESC
		");

		$this->_hierarchy = Arrays::intoHierarchy($this->_hierarchy);

		return $this->_hierarchy;
	}


	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}
}