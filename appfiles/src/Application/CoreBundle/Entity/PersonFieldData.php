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
 * Data storage for fields attached to Person
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="person_field_data")
 */
class PersonFieldData extends FormFieldData
{
	/**
	 * The field the data maps to.
	 *
	 * @var int
	 * @Column(name="person_field_id", type="integer")
	 */
	protected $person_field_id;

	/**
	 * The form field this is attached to
	 *
	 * @var \Application\CoreBundle\Entity\PersonField
	 * @ManyToOne(targetEntity="PersonField")
	 * @JoinColumn(name="person_field_id", referencedColumnName="id")
	 */
	protected $field = null;

	/**
	 * The person ID
	 *
	 * @var int
	 * @Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * The form field this is attached to
	 *
	 * @var \Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;
}