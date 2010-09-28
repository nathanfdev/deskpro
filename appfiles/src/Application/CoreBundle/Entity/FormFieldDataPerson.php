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
 * Form field data attached to Person records
 *
 * @Entity
 * @Table(name="form_field_data_person")
 */
class FormFieldDataPerson extends FormFieldData
{
	/**
	 * The person record.
	 * 
	 * @var Person
	 * @OneToOne(targetEntity="Person")
	 * @JoinColumn(name="record_id", referencedColumnName="id")
	 */
	protected $person;
}