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

use Application\DeskPRO\Entity\LabelBlob;

/**
 * A blob is just a pointer to data.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="blobs")
 */
class Blob extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

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
	 * @ORM_Mapping\Column(name="authcode", type="string", length="20")
	 */
	protected $authcode;

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
	 * @ORM_Mapping\OneToMany(targetEntity="LabelBlob", mappedBy="blob", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	protected $_label_manager = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->authcode = Strings::random(20, Strings::CHARS_KEY);
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
		return App::get('router')->generate('serve_blob', array('blob_auth_id' => $this->getAuthId()), $absolute);
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
		return App::get('router')->generate('serve_blob', array('blob_auth_id' => $this->getAuthId(), 's' => $size), $absolute);
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