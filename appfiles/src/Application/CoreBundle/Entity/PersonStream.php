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
 * Ticket log items
 *
 * @Entity
 * @Table(name="person_stream")
 */
class PersonStream extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @OneToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @Column(name="action_type", type="string", length=40)
	 */
	protected $action_type;

	/**
	 * @var string
	 * @Column(name="summary", type="string", length=255)
	 */
	protected $summary;

	/**
	 * @var string
	 * @Column(name="details", type="array")
	 */
	protected $details = array();

	/**
	 * @var \DateTime
	 * @Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/** @PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}