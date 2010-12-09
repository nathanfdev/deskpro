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

/**
 * Data storage for fields attached to Person
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="person_field_data")
 */
class PersonFieldData extends FormFieldData
{
	/**
	 * The field the data maps to.
	 *
	 * @var int
	 * @orm:Column(name="person_field_id", type="integer")
	 */
	protected $person_field_id;

	/**
	 * The form field this is attached to
	 *
	 * @var \Application\DeskPRO\Entity\PersonField
	 * @orm:ManyToOne(targetEntity="PersonField")
	 * @orm:JoinColumn(name="person_field_id", referencedColumnName="id")
	 */
	protected $field = null;

	/**
	 * The person ID
	 *
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * The form field this is attached to
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;
	


	/**
	 * Add a child data item
	 *
	 * @param FormFieldData $data
	 */
	public function addChildData(FormFieldData $data)
	{
		$this->data_children->add($data);
		$data['parent'] = $this;
		$data['field'] = $this->field;
		$this->person->addFieldData($data);
	}



	/**
	 * Create a new data object to store child-data for this field.
	 *
	 * @return FormFieldData
	 */
	public function createChildInstance()
	{
		$classname = get_class($this);

		$obj = new $classname();
		$obj['parent'] = $this;
		$obj['field'] = $this->field;

		return $obj;
	}
}