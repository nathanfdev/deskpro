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

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Base class used for storing custom field data.
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class CustomDataAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * IMPLEMENT IN CHILD CLASS
	 * The form field this is attached to
	 *
	 * @var \Application\DeskPRO\Entity\CustomDefXXX
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefXXX")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id")
	 */
	//protected $field = null;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Application\DeskPRO\Entity\Xxx
	 * @ORM_Mapping\ManyToOne(targetEntity="xxx")
	 * @ORM_Mapping\JoinColumn(name="xxx_id", referencedColumnName="id")
	 */
	//protected $xxx;

	/**
	 * User numeric data
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="value", type="integer")
	 */
	protected $value = 0;

	/**
	 * User string data
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="input", type="text")
	 */
	protected $input = '';



	/**
	 * Get the value or input.
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->value ? $this->value : $this->input;
	}


	public function getFieldId()
	{
		return $this->field['id'];
	}
}