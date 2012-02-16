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
 * Stores data from importing for long-term (ie unimplemented features).
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ImportDataStore")
 * @ORM_Mapping\Table(name="import_datastore")
 */
class ImportDataStore extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The type of id/thing/whatever this is mapping.
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="typename", type="dpblob", length=80)
	 */
	protected $typename;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data = array();
}
