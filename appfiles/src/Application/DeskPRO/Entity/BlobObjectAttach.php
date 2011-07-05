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

use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Numbers;

/**
 * This is a simplified lookup table to see which blobs are attached to which objects.
 * This is to aid the media browser.
 *
 * @orm:Entity
 * @orm:Table(name="blob_object_attach")
 */
class BlobObjectAttach extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var bool
	 * @orm:Column(name="object_type", type="string", length="100")
	 * @orm:Id
	 */
	protected $object_type;

	/**
	 * @var bool
	 * @orm:Column(name="object_id", type="integer")
	 */
	protected $object_id;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @orm:ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @orm:JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;

	public function getObject()
	{
		return App::findEntity($this->object_type, $this->object_id);
	}
}