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
 * Base class used for storing custom field data.
 *
 * @MappedSuperclass
 */
abstract class CustomDataAbstract extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="field_id", type="integer")
	 */
	protected $field_id;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 * The form field this is attached to
	 *
	 * @var \Application\CoreBundle\Entity\CustomDefXXX
	 * @ManyToOne(targetEntity="CustomDefXXX")
	 * @JoinColumn(name="field_id", referencedColumnName="id")
	 */
	//protected $field = null;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 *
	 * @var int
	 * @Id @Column(name="xxx_id", type="integer")
	 */
	//protected $xxx_id;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Application\CoreBundle\Entity\Xxx
	 * @ManyToOne(targetEntity="xxx")
	 * @JoinColumn(name="xxx_id", referencedColumnName="id")
	 */
	//protected $xxx;

	/**
	 * User numeric data
	 *
	 * @var array
	 * @Column(name="value", type="integer")
	 */
	protected $value = 0;

	/**
	 * User string data
	 *
	 * @var array
	 * @Column(name="input", type="text")
	 */
	protected $input = 0;
}