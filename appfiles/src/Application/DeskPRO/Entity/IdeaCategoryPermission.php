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

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Idea category permissions
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\IdeaCategoryPermission")
 * @ORM_Mapping\Table(name="idea_category_permissions")
 */
class IdeaCategoryPermission extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 * @ORM_Mapping\ManyToOne(targetEntity="Usergroup", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $usergroup = null;

	/**
	 * @var \Application\DeskPRO\Entity\IdeaCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="IdeaCategory", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $category = null;
}