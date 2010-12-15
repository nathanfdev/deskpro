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

/**
 * When blobs are stored in the database, they are stored as muliple parts in this table.
 *
 * (Ordering is by id ASC)
 *
 * @orm:Entity
 * @orm:Table(name="blobs_storage")
 */
class BlobStorage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var int
	 * @orm:Column(name="blob_id", type="integer")
	 */
	protected $blob_id;

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @TODO This needs to be a binary type
	 *
	 * @var string
	 * @orm:Column(name="data", type="text")
	 */
	protected $data;
}