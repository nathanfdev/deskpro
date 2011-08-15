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
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Settings used by the system.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="style_resources")
 */
abstract class StyleResource extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;


	/**
	 * The style this template belongs to
	 *
	 * @var Style
	 * @ORM_Mapping\OneToOne(targetEntity="Style")
	 * @ORM_Mapping\JoinColumn(name="style_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $style;


	/**
	 * Title of the style
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;


	/**
	 * The resource value.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="resource", type="text", nullable=true)
	 */
	protected $resource = null;


	/**
	 * The resource value.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="raw_resource", type="text", nullable=true)
	 */
	protected $raw_resource = null;


	/**
	 * Any resource data such as options or config data used in sub-classes.
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="resource_data", type="array", nullable=true)
	 */
	protected $resource_data = null;


	/**
	 * Any user data such as customizations or defined options.
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="user_data", type="array", nullable=true)
	 */
	protected $user_data = null;


	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="updated_at",type="datetime")
	 */
	protected $updated_at;



	/**
	 * Get a 'simple' version of the title of this resource that can be used in
	 * URLs and such.
	 *
	 * @return string
	 */
	public function getSimpleTitle()
	{
		return Strings::slugifyTitle($this->title);
	}



	/**
	 * Get a filename for this resource
	 * 
	 * @return string
	 */
	public function getFilename()
	{
		return $this->getSimpleTitle();
	}



	/**
	 * Get headers to send to the browser when we need to serve this resource.
	 * The returned array should be an array map of headers to values.
	 *
	 * @return array
	 */
	public function getResourceHeaders()
	{
		return Web::getAttachmentHeaders(
			$this->getFilename(),
			false
		);
	}



	/**
	 * Write resource data to the provided stream.
	 *
	 * @param resource $stream_p A filestram (i.e., usually STDOUT)
	 * @return int Bytes successfully written or false on error
	 */
	public function writeResourceData($stream_p)
	{
		return @fwrite($stream_p, $this->resource);
	}


	
	/** @ORM_Mapping\PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @ORM_Mapping\PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}