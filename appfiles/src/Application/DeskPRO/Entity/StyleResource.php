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
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Settings used by the system.
 *
 * @orm:Entity
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="resource_type", type="string")
 * @DiscriminatorMap({"css" = "StyleResourceCss"})
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="style_resources")
 */
abstract class StyleResource extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * The style this template belongs to
	 *
	 * @var Style
	 * @orm:OneToOne(targetEntity="Style")
	 * @orm:JoinColumn(name="style_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $style;


	/**
	 * Title of the style
	 *
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;


	/**
	 * The resource value.
	 *
	 * @var string
	 * @orm:Column(name="resource", type="text", nullable=true)
	 */
	protected $resource = null;


	/**
	 * The resource value.
	 *
	 * @var string
	 * @orm:Column(name="raw_resource", type="text", nullable=true)
	 */
	protected $raw_resource = null;


	/**
	 * Any resource data such as options or config data used in sub-classes.
	 *
	 * @var array
	 * @orm:Column(name="resource_data", type="array", nullable=true)
	 */
	protected $resource_data = null;


	/**
	 * Any user data such as customizations or defined options.
	 *
	 * @var array
	 * @orm:Column(name="user_data", type="array", nullable=true)
	 */
	protected $user_data = null;


	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 * @orm:Column(name="updated_at",type="datetime")
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


	
	/** @orm:PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @orm:PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}