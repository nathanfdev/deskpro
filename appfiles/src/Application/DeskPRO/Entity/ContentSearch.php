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

/**
 * Combined search index of content
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="content_search")
 */
class ContentSearch extends \Application\DeskPRO\Domain\DomainObject
{
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

	/**
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content;
}