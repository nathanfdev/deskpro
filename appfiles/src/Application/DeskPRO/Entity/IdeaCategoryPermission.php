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

namespace Application\DeskPRO\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Idea category permissions
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\IdeaCategoryPermission")
 * @orm:Table(name="idea_category_permissions")
 */
class IdeaCategoryPermission extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 * @orm:ManyToOne(targetEntity="Usergroup", fetch="EAGER")
	 * @orm:JoinColumn(name="usergroup_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $usergroup = null;

	/**
	 * @var \Application\DeskPRO\Entity\IdeaCategory
	 * @orm:ManyToOne(targetEntity="IdeaCategory", fetch="EAGER")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $category = null;
}