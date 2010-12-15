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
 * A blob is just a pointer to data.
 *
 * @orm:Entity
 * @orm:Table(name="blobs")
 */
class Blob extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The path to the file if it's not stored in the database.
	 *
	 * @var string
	 * @orm:Column(name="save_path", type="string", length=255, nullable=true)
	 */
	protected $save_path = null;

	/**
	 * The original filename
	 *
	 * @var string
	 * @orm:Column(name="filename", type="string", length=120)
	 */
	protected $filename = null;

	/**
	 * The file size
	 *
	 * @var int
	 * @orm:Column(name="filesize", type="integer")
	 */
	protected $filesize;

	/**
	 * The files mimetype
	 *
	 * @var string
	 * @orm:Column(name="content_type", type="string", length=50)
	 */
	protected $content_type = null;
}