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
 * Custom ticket data
 * 
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="custom_data_person")
 */
abstract class CustomDataPerson extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\CustomDefPerson
	 * @orm:ManyToOne(targetEntity="CustomDefTicket")
	 * @orm:JoinColumn(name="field_id", referencedColumnName="id")
	 */
	protected $field = null;

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;
}