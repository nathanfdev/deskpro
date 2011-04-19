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
 * Category permissions
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ArticleCategoryPermission")
 * @orm:Table(name="article_category_permissions")
 */
class ArticleCategoryPermission extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 * @orm:ManyToOne(targetEntity="Usergroup", fetch="EAGER")
	 * @orm:JoinColumn(name="usergroup_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $usergroup = null;

	/**
	 * @var \Application\DeskPRO\Entity\ArticleCategory
	 * @orm:ManyToOne(targetEntity="ArticleCategory", fetch="EAGER")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $category = null;
}