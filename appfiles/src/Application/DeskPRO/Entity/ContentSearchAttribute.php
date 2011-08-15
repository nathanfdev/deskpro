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
 * Attributes or various other fields that are searchable on some type
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="content_search_attribute")
 */
class ContentSearchAttribute extends \Application\DeskPRO\Domain\DomainObject
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
	 * The name of the attribute like "somefield"
	 * 
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="attribute_id", type="string", length=200)
	 */
	protected $attribute_id;

	/**
	 * The searchable content of the attribuet
	 * 
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="content", type="string", length=200)
	 */
	protected $content;
}