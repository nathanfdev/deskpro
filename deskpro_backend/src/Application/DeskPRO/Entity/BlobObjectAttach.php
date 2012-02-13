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

use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Numbers;

/**
 * This is a simplified lookup table to see which blobs are attached to which objects.
 * This is to aid the media browser.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="blob_object_attach")
 */
class BlobObjectAttach extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="object_type", type="string", length=100)
	 * @ORM_Mapping\Id
	 */
	protected $object_type;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="object_id", type="integer")
	 */
	protected $object_id;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;

	public function getObject()
	{
		return App::findEntity($this->object_type, $this->object_id);
	}
}
