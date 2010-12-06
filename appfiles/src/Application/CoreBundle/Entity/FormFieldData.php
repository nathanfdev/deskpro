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
 * @orm:MappedSuperclass
 */
abstract class FormFieldData extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The parent ID for multi-field fields.
	 *
	 * @var int
	 * @orm:Column(name="parent_id", type="integer", nullable=true)
	 */
	protected $parent_id = null;

	/**
	 * User data, or the 'value' of the field. This data is passed to the form
	 * fields.
	 *
	 * @var array
	 * @orm:Column(name="data", type="array", nullable=false)
	 */
	protected $data;

	/**
	 * Related data
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="FormFieldData", mappedBy="parent_id")
	 */
	protected $data_children = null;



	/**
	 * Sets data from the form field
	 *
	 * @param mixed $data
	 */
	public function setData($data)
	{
		if (!is_array($data)) {
			$data = array('value' => $data);
		}

		$this->data = $data;
	}



	/**
	 * Add a child data item
	 *
	 * @param FormFieldData $data
	 */
	public function addChildData(FormFieldData $data)
	{
		$this->data_children->add($data);
		$data['parent'] = $this;
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



	/**
	 * Render this field in a given context
	 * 
	 * @param string $context
	 * @return string
	 */
	public function renderContext($context = 'html')
	{
		$field_handler = $this['field']->getHandler();
		return $field_handler->renderContext($context, $this);
	}
}