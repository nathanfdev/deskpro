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
 * When blobs are stored in the database, they are stored as muliple parts in this table.
 *
 * (Ordering is by id ASC)
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="blobs_storage")
 */
class BlobStorage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="blob_id", type="integer")
	 */
	protected $blob_id;

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="dpblob", length=4294967295)
	 */
	protected $data;
}
