<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

/**
 * Attachments are binary file data that can be attached to various things.
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="attachments",
 *     indexes={@orm:Index(name="object_idx", columns={"object_type","object_id"})}
 * )
 */
class Attachment extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The type of object this is attached to (should be the table name of
	 * the super type, eg: tickets, people, organizations).
	 *
	 * @var string
	 * @orm:Column(name="object_type", type="string", length=50)
	 */
	protected $object_type;

	/**
	 * The ID of the object this is attached to.
	 * 
	 * @var int
	 * @orm:Column(name="object_id", type="integer")
	 */
	protected $object_id;

	/**
	 * The path to the file if it's not stored in the database.
	 *
	 * @var string
	 * @orm:Column(name="save_path", type="string", length=255)
	 */
	protected $save_path = '';

	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * Who created the attachment
	 * 
	 * @var \Application\CoreBundle\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The original filename
	 *
	 * @var string
	 * @orm:Column(name="filename", type="string", length=120)
	 */
	protected $filename;
	
	/**
	 * The file size
	 *
	 * @var int
	 * @orm:Column(name="filesize", type="integer")
	 */
	protected $language_id;

	/**
	 * The files mimetype
	 *
	 * @var string
	 * @orm:Column(name="content_type", type="string", length=50)
	 */
	protected $content_type;



	/** @orm:PrePersist */
	public function _prePersist()
	{
		$this->date_created = new \DateTime();
	}
}
