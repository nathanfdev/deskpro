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

use Doctrine\ORM\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\IdeaStatusCategory as IdeaStatusCategoryEntity;

class IdeaStatusCategory extends EntityRepository
{
	protected $active_cats = null;
	protected $closed_cats = null;

	public function reload()
	{
		$this->active_cats = $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:IdeaStatusCategory c INDEX BY c.id
			WHERE c.status_type = ?1
			ORDER BY c.display_order ASC
		")->setParameter(1, 'active')->execute();

		$this->closed_cats = $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:IdeaStatusCategory c INDEX BY c.id
			WHERE c.status_type = ?1
			ORDER BY c.display_order ASC
		")->setParameter(1, 'closed')->execute();
	}

	public function getActiveCategories()
	{
		if ($this->active_cats === null) $this->reload();
		return $this->active_cats;
	}

	public function getClosedCategories()
	{
		if ($this->closed_cats === null) $this->reload();
		return $this->closed_cats;
	}
}
