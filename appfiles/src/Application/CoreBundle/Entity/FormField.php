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
	 * The title. Note this should be a phrase key, not an actual string.
	 * 
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title = '';

	/**
	 * The system typename. When defining fields, we may need specialized interfaces
	 * to build up each kind of field. This is the system name.
	 *
	 * @var string
	 * @Column(name="typeclass", type="string", length=255)
	 */
	protected $typeclass = 'Text';

	/**
	 * Options for the field
	 * 
	 * @Column(name="options", type="array")
	 */
	protected $options = array();

	/**
	 * Field children
	 * 
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @OneToMany(targetEntity="FormField", mappedBy="parent_id")
	 */
	protected $field_children = null;

	

	/**
	 * Get the DeskPRO form field object that knows how to render data etc.
	 *
	 * @return DeskPRO\FormField\Type\Type
	 */
	public function getFormFieldType()
	{
		$classname = $this['typeclass'];

		$formfield = new $classname($this);

		return $formfield;
	}


	
	/**
	 * Get the full classname to the type.
	 *
	 * @return string
	 */
	public function getTypeclass()
	{
		$classname = $this->typeclass;

		// Easy check for full namepsaced classname
		// If not a full classname, then we assume its a DeskPRO class
		if (strpos($classname, '\\') === false) {
			$classname = 'DeskPRO\\FormField\\Type\\' . $classname;
		}

		return $classname;
	}
}