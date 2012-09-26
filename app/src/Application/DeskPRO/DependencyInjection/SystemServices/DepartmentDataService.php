<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class DepartmentDataService extends BaseRepositoryService
{
	protected $has_init = false;
	protected $cats;
	protected $cat_ids = array();
	protected $filtered_nodes = array();

	/**
	 * @var \Application\DeskPRO\Translate\Translate
	 */
	protected $translator;

	public static function create(DeskproContainer $container, array $options = null)
	{
		if (!$options) $options = array();
		$options['entity'] = 'Application\\DeskPRO\\Entity\\Department';
		$options['translator'] = $container->getTranslator();

		$em = $container->getEm();
		$o = new static($em, $options);
		return $o;
	}

	protected function init()
	{
		$this->translator = $this->options['translator'];
	}

	public function get($dep_id)
	{
		$this->preload();
		return isset($this->cats[$dep_id]) ? $this->cats[$dep_id] : null;
	}

	protected function preload()
	{
		if ($this->has_init) {
			return;
		}
		$this->has_init = true;

		$this->cats = $this->em->createQuery("
			SELECT d
			FROM DeskPRO:Department d INDEX BY d.id
			ORDER BY d.display_order ASC
		")->execute();
		$this->em->getUnitOfWork()->markAsPreloaded('DeskPRO:Department');

		$cats = array();

		// force hydration
		foreach ($this->cats as $c) {
			$this->cat_ids[] = $c->getId();
			$c->getTitle();

			$cats[$c->getId()] = array(
				'id' => $c->getId(),
				'parent_id' => $c->parent ? $c->parent->getId() : 0,
				'title' => $c->getTitle()
			);
		}
		foreach ($this->cats as $c) {
			$c->children->initialize();
		}

		$this->repos->getInHierarchy($cats);
	}

	public function getNames($for_ids = null)
	{
		$this->preload();

		$ret = array();

		if ($for_ids) {
			foreach ($for_ids as $cid) {
				$ret[$cid] = $this->translator->getPhraseObject($this->get($cid), 'title');
			}
		} else {
			foreach ($this->cat_ids as $cid) {
				$ret[$cid] = $this->translator->getPhraseObject($this->get($cid), 'title');
			}
		}

		return $ret;
	}

	public function getByIds(array $ids, $keep_order = false)
	{
		$this->preload();
		$ret = array();

		foreach ($ids as $id) {
			if (isset($this->cats[$id])) {
				$ret[$id] = $this->cats[$id];
			}
		}

		return $ret;
	}

	public function getChildren($category = null, $direct = true)
	{
		$this->preload();
		$ids = $this->repos->getChildrenIds($category, $direct);
		if (!$ids) {
			return array();
		}

		return $this->getByIds($ids);
	}

	public function getPersonDepartments(\Application\DeskPRO\Entity\Person $person_context, $app, array $allow_ids = array())
	{
		$key = md5($person_context->getId() . '.' . $app);

		if (isset($this->filtered_nodes[$key])) {
			return $this->filtered_nodes[$key];
		}

		if ($allow_ids) {
			foreach (array_values($allow_ids) as $id) {
				$d = $this->get($id);
				if ($d && $d->parent) {
					$allow_ids[] = $d->parent->getId();
				}
			}

			$allow_ids = array_unique($allow_ids);
			$allow_ids = array_combine(array_values($allow_ids), array_values($allow_ids));
		}

		$filter = function ($c) use ($person_context, $app, $allow_ids) {
			if (isset($allow_ids[$c->getId()])) {
				return true;
			}
			return $person_context->getPermissionsManager()->Departments->isAllowed($c->getId(), $app);
		};

		if (!$allow_ids) {
			$this->filtered_nodes[$key] = \Application\DeskPRO\Tree\TreeProxyHasPhraseName::makeTreeProxyArray($this->getRootNodes(), $filter);
			return $this->filtered_nodes[$key];
		} else {
			$nodes = \Application\DeskPRO\Tree\TreeProxyHasPhraseName::makeTreeProxyArray($this->getRootNodes(), $filter);
			return $nodes;
		}
	}

	public function getRootNodes()
	{
		$this->preload();
		$root_ids = $this->repos->getRootNodeIds();

		if (!$root_ids) {
			return array();
		}

		return $this->getByIds($root_ids);
	}

	public function getPath($category)
	{
		$this->preload();
		$ids = $this->repos->getPathIds($category);

		if (!$ids) {
			return array();
		}

		return $this->getByIds($ids);
	}


	public function __call($method, array $args = array())
	{
		$this->preload();
		return parent::__call($method, $args);
	}
}