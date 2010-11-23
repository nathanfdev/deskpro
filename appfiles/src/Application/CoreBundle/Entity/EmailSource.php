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
 * Raw email sources
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="email_sources",
 *     indexes={@Index(name="object_idx", columns={"object_type", "object_id"})}
 * )
 */
class EmailSource extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="gateway_id", type="integer")
	 */
	protected $gateway_id;

	/**
	 * @var \Application\CoreBundle\Entity\EmailGateway
	 * @OneToOne(targetEntity="EmailGateway")
	 * @JoinColumn(name="gateway_id", referencedColumnName="id")
	 */
	protected $gateway = null;

	/**
	 * The type of object this is attached to (should be the table name of
	 * the super type, eg: tickets, people, organizations).
	 *
	 * @var string
	 * @Column(name="object_type", type="string", length=50)
	 */
	protected $object_type;

	/**
	 * The ID of the object this is attached to.
	 *
	 * @var int
	 * @Column(name="object_id", type="integer")
	 */
	protected $object_id;

	/**
	 * Just the headers portion of the email
	 *
	 * @var string
	 * @Column(name="headers", type="string", length=1000)
	 */
	protected $headers;

	/**
	 * The current status of the message:
	 * - inserted: Only inserted
	 * - complete: Fully processed
	 *
	 * @var string
	 * @Column(name="status", type="string", length=15)
	 */
	protected $status = 'inserted';

	/**
	 * The path to the raw email if it was saved to the filesystem
	 *
	 * @var string
	 * @Column(name="save_path", type="string", length=255)
	 */
	protected $save_path = '';

	/**
	 * @var \DateTime
	 * @Column(name="date_created",type="datetime")
	 */
	protected $date_created;


	/** @PrePersist */
	public function _prePersist()
	{
		$this->date_created = new \DateTime();
	}
}
