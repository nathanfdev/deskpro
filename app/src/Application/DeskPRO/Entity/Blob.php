<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Numbers;

use Application\DeskPRO\Entity\LabelBlob;

/**
 * A blob is just a pointer to data.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Blob")
 * @ORM_Mapping\Table(name="blobs")
 */
class Blob extends \Application\DeskPRO\Domain\DomainObject
{
	const STORAGE_LOC_FILESYSTEM = 'fs';
	const STORAGE_LOC_S3 = 's3';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * A unique system name for the blob.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="sys_name", type="string", length=100, nullable=true)
	 */
	protected $sys_name = null;

	/**
	 * Sometimes we might have multiple versions of a file. For example, if a file has been
	 * cropped then the cropped file is saved as its own blob, but the original
	 * is linked here.
	 *
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="original_blob_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $original_blob;

	/**
	 * If not stored in the database, this is where the file is stored.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="storage_loc", type="string", length=50, nullable=true)
	 */
	protected $storage_loc = null;

	/**
	 * The path to the file if it's not stored in the database.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="save_path", type="string", length=255, nullable=true)
	 */
	protected $save_path = null;

	/**
	 * The original filename
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="filename", type="string", length=120)
	 */
	protected $filename = null;

	/**
	 * The file size
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="filesize", type="integer")
	 */
	protected $filesize;

	/**
	 * The files mimetype
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="content_type", type="string", length=50)
	 */
	protected $content_type = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="authcode", type="string", length=50)
	 */
	protected $authcode;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="blob_hash", type="string", length=40)
	 */
	protected $blob_hash;

	/**
	 * Is this a media upload (appears in the media browser etc). These are files that were
	 * uploaded and are attached to things.
	 *
	 * @ORM_Mapping\Column(name="is_media_upload", type="boolean")
	 */
	protected $is_media_upload = false;

	/**
	 * The title of this file used in interfaces if its a media upload
	 *
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title = '';

	/**
	 * If this type of file has dimentions, the width
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="dim_w", type="integer")
	 */
	protected $dim_w = 0;

	/**
	 * If this type of file has dimentions, the height
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="dim_h", type="integer")
	 */
	protected $dim_h = 0;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_temp", type="boolean")
	 */
	protected $is_temp = false;

	/**
	 * The date this blob should be automatically cleaned
	 * @ORM_Mapping\Column(name="date_cleanup",type="datetime", nullable=true)
	 */
	protected $date_cleanup;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelBlob", mappedBy="blob", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	protected $_label_manager = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->authcode = Strings::random(20, Strings::CHARS_KEY_ALPHA);
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}


	public function setFilename($filename)
	{
		$old = $this->filename;
		$this->filename = $filename;
		$this->_onPropertyChanged('filename', $old, $this->filename);

		// Try to guess content typ based off of filename exts
		if (!$this->content_type) {
			$ct = \Orb\Data\ContentTypes::getContentTypeFromFilename($this->filename);
			if ($ct) {
				$this['content_type'] = $ct;
			}
		}
	}


	/**
	 * Get the file extension
	 *
	 * @return string
	 */
	public function getExtension()
	{
		$pos = strrpos($this->filename, '.');
		if ($pos === false) return '';

		return substr($this->filename, $pos+1);
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
	 * Get the type of image this is, or null if its not an image.
	 *
	 * @return string
	 */
	public function getImageType()
	{
		switch ($this->content_type) {
			case 'image/jpg':
			case 'image/jpeg':
				return 'jpeg';
			case 'image/gif':
				return 'gif';
			case 'image/png':
				return 'png';
		}

		return null;
	}


	/**
	 * Get the filesize with B, KB, GB etc suffix.
	 */
	public function getReadableFilesize()
	{
		return Numbers::filesizeDisplay($this->filesize);
	}


	/**
	 * Get the id-auth combo typically used in urls.
	 *
	 * @return string
	 */
	public function getAuthId()
	{
		return $this->authcode;
	}


	/**
	 * Get the standard download URL for this blob.
	 *
	 * @param bool $absolute
	 * @return string
	 */
	public function getDownloadUrl($absolute = false)
	{
		return App::get('router')->generate('serve_blob', array('blob_auth_id' => $this->getAuthId(), 'filename' => $this->getFilenameSafe()), $absolute);
	}


	/**
	 * Get a thumbnail for this blob (if its an image)
	 *
	 * @param int $size
	 * @param bool $absolute
	 * @return string
	 */
	public function getThumbnailUrl($size = 50, $absolute = false)
	{
		if (!$this->isImage()) {
			throw new \InvalidArgumentException("You can't get a thumbnail for a non-image");
		}
		return App::get('router')->generate('serve_blob', array('blob_auth_id' => $this->getAuthId(), 'filename' => $this->filename, 's' => $size), $absolute);
	}

	/**
	 * Get a safe version of a filename. That is the same filename with all "weird" characters removed.
	 *
	 * @return string
	 */
	public function getFilenameSafe()
	{
		$filename_safe = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $this->filename);
		$filename_safe = preg_replace('#\-{2,}#', '-', $filename_safe);

		return $filename_safe;
	}

	/**
	 * @return string
	 */
	public function getDisplayTitle()
	{
		if ($this->title) {
			return $this->title;
		}

		return $this->filename;
	}

	public function addLabel(LabelBlob $label)
	{
		$label['blob'] = $this;
		$this->labels->add($label);
	}


	/**
	 * @return \Application\DeskPRO\Labels\LabelManager
	 */
	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelBlob');
		}

		return $this->_label_manager;
	}
}
