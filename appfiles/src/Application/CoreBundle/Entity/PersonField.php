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
 * A PersonField describes a type of field that is set on a Person, and how the data
 * is to be treated/inputted/transformed etc.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="person_fields")
 */
class PersonField extends \DeskPRO\Domain\DomainObject
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
	 * The type of field. TODO: Later maybe this is a classname?
	 *
	 * @var string
	 * @Column(name="field_type", type="string", length=255)
	 */
	protected $field_type = 'free';
}