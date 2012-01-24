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
 * A general map that maps old IDs to new IDs
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ImportMap")
 * @ORM_Mapping\Table(name="import_map")
 */
class ImportMap extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The type of id/thing/whatever this is mapping.
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="typename", type="blob", length=80)
	 */
	protected $typename;

	/**
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="old_id", type="blob", length=80)
	 */
	protected $old_id = 0;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="new_id", type="blob", length=80)
	 */
	protected $new_id = 0;
}
