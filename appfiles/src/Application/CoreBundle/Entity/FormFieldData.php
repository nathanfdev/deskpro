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
	 * User data, or the 'value' of the field. This data is passed to the form
	 * fields.
	 *
	 * @var array
	 * @Column(name="data", type="array", nullable=false)
	 */
	protected $data;

	/**
	 * Related data
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @OneToMany(targetEntity="FormFieldData", mappedBy="parent_id")
	 */
	protected $data_children = null;



	/**
	 * Get the value of this field rendered to HTML
	 *
	 * @return string
	 */
	public function getDisplayHtml()
	{
		$field_handler = $this['field']->getHandler();
		return $field_handler->renderHtml($this);
	}

	

	/**
	 * Get the value of this field rendered to plain text.
	 * 
	 * @return string
	 */
	public function getDisplayText()
	{
		$field_handler = $this['field']->getHandler();
		return $field_handler->renderText($this);
	}
}