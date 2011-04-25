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
 * News category permissions
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\NewsCategoryPermission")
 * @orm:Table(name="news_category_permissions")
 */
class NewsCategoryPermission extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 * @orm:ManyToOne(targetEntity="Usergroup", fetch="EAGER")
	 * @orm:JoinColumn(name="usergroup_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $usergroup = null;

	/**
	 * @var \Application\DeskPRO\Entity\NewsCategory
	 * @orm:ManyToOne(targetEntity="NewsCategory", fetch="EAGER")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $category = null;
}