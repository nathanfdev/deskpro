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

	/**
	 * @var int
	 * @orm:Column(name="authcode", type="string", length="20")
	 */
	protected $authcode;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->authcode = Strings::random(20, Strings::CHARS_KEY);
	}



	/**
	 * Is the file an image?
	 *
	 * @return bool
	 */
	public function isImage()
	{
		switch ($this->content_type) {
			case 'image/jpg':
			case 'image/jpeg':
				return true;
			case 'image/gif':
				return true;
			case 'image/png':
				return true;
		}

		return false;
	}



	/**
	 * Get the id-auth combo typically used in urls.
	 *
	 * @return string
	 */
	public function getAuthId()
	{
		return $this->id . '-' . $this->authcode;
	}



	/**
	 * Get the standard download URL for this blob.
	 *
	 * @param bool $absolute
	 * @return string
	 */
	public function getDownloadUrl($absolute = false)
	{
		return App::get('router')->generate('serve_blob', 'blob_auth_id', $absolute);
	}
}