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
 * Base class used for storing custom field data.
 *
 * @orm:MappedSuperclass
 */
abstract class CustomDataAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 *
	 * @var int
	 * @orm:Column(name="field_id", type="integer")
	 */
	//protected $field_id;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 * The form field this is attached to
	 *
	 * @var \Application\DeskPRO\Entity\CustomDefXXX
	 * @orm:ManyToOne(targetEntity="CustomDefXXX")
	 * @orm:JoinColumn(name="field_id", referencedColumnName="id")
	 */
	//protected $field = null;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 *
	 * @var int
	 * @orm:Column(name="xxx_id", type="integer")
	 */
	//protected $xxx_id;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Application\DeskPRO\Entity\Xxx
	 * @orm:ManyToOne(targetEntity="xxx")
	 * @orm:JoinColumn(name="xxx_id", referencedColumnName="id")
	 */
	//protected $xxx;

	/**
	 * User numeric data
	 *
	 * @var int
	 * @orm:Column(name="value", type="integer")
	 */
	protected $value = 0;

	/**
	 * User string data
	 *
	 * @var string
	 * @orm:Column(name="input", type="text")
	 */
	protected $input = 0;

	

	/**
	 * Get the value or input.
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->value ? $this->value : $this->input;
	}


	public function setData($data)
	{
		if ($this->field->getHandler()->getStorageDataType() == 'integer') {
			$this->value = $data;
		} else {
			$this->input = $data;
		}
	}
}