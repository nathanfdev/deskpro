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

/**
 * Associates an import word with a piece of content. When someone searches
 * for the word, the content is displayed a the top of result listings.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\SearchStickyResult")
 * @ORM_Mapping\Table(name="search_sticky_result")
 * @Orm:HasLifecycleCallbacks
 */
class SearchStickyResult extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @ORM_Mapping\Column(name="word", type="string", length=150)
	 * @ORM_Mapping\Id
	 */
	protected $word;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="object_type", type="string", length=100)
	 * @ORM_Mapping\Id
	 */
	protected $object_type;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="object_id", type="integer")
	 * @ORM_Mapping\Id
	 */
	protected $object_id = null;
}
