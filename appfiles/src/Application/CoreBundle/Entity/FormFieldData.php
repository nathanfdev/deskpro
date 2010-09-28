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
 * Base class used for storing custom field data. Each thing in the database
 * will have it's own table for storing data for performance reasons, but they
 * should all extend this base class.
 *
 * @MappedSuperclass
 */
abstract class FormFieldData extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The field the data maps to
	 *
	 * @var int
	 * @Column(name="form_field_id", type="integer")
	 */
	protected $form_field_id;

	/**
	 * The field the data maps to
	 *
	 * @var \Application\CoreBundle\FormField
	 * @OneToOne(targetEntity="FormField")
	 * @JoinColumn(name="form_field_id", referencedColumnName="id")
	 */
	protected $form_field;

	/**
	 * The database record of this type the data belongs to. Subclasses
	 * should add a 'record' type that points to the proper entity.
	 *
	 * @var int
	 * @Column(name="form_field_id", type="integer")
	 */
	protected $record_id;

	/**
	 * User data, or the 'value' of the field. This data is passed to the form
	 * fields.
	 *
	 * @var array
	 * @Column(name="data", type="array", nullable=false)
	 */
	protected $data;

	/**
	 * Indexed string data that will be used for searching.
	 *
	 * @var string
	 * @Column(name="indexed_str", type="text", nullable=true)
	 */
	protected $indexed_str = null;

	/**
	 * Indexed integer data that will be used for searchign.
	 * 
	 * @var int
	 * @Column(name="indexed_int", type="integer", nullable=true)
	 */
	protected $indexed_int = null;
}