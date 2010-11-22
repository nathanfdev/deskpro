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
 * Ticket categories
 *
 * @Entity
 * @Table(name="ticket_categories")
 */
class TicketCategory extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="department_id", type="integer")
	 */
	protected $department_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Department
	 * @OneToOne(targetEntity="Department")
	 * @JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var int
	 * @Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;
}