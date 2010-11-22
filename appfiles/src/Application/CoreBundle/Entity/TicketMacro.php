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
 * Departments
 *
 * @Entity
 * @Table(name="ticket_macros")
 */
class Department extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @Column(name="labels", type="string", length=1000)
	 */
	protected $labels;

	/**
	 * @var bool
	 * @Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var bool
	 * @Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @Column(name="actions", type="array")
	 */
	protected $actions;
}