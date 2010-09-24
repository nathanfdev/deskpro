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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A form field is any custom field that can be attached to anything in the system.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="form_fields")
 */
class FormField extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The parent ID for multi-field fields.
	 *
	 * @var int
	 * @Column(name="parent_id", type="integer", nullable=true)
	 */
	protected $parent_id = null;
	
	/**
	 * The field classname
	 *
	 * @var string
	 * @Column(name="field_classname", type="string", length=255)
	 */
	protected $field_classname = 'Orb\\Form\\Field\\Text';

	/**
	 * Options to pass to the field
	 * 
	 * @Column(name="field_options", type="array")
	 */
	protected $field_options = array();

	/**
	 * Field children
	 * 
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @OneToMany(targetEntity="FormField", mappedBy="parent_id")
	 */
	protected $field_children = null;

	

	/**
	 * Get the field instance. Note that this value is NOT cached, you should
	 * assign it to a local var always.
	 *
	 * TODO: handle setting up validators and transformers, if nec
	 *
	 * @return Orb\Field\Field
	 */
	public function getField()
	{
		$field_classname = $this->field_classname;

		$options = $this->field_options;
		$options['name'] = 'field_' . $this->id;
		$field = new $field_classname($options);

		if ($this->field_children) {
			foreach ($this->field_children as $child) {
				$field->addField($child->getField());
			}
		}

		return $field;
	}
}