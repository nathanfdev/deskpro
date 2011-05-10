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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * Profile-related display information
 *
 * @orm:Entity
 * @orm:Table(name="person_display_elements")
 */
class PersonDisplayElement extends \Application\DeskPRO\Domain\DomainObject
{
	const ZONE_AGENT = 'agent';
	const ZONE_USER  = 'user';

	const ELEMENT_FIELD = 'field';
	const ELEMENT_WIDGET = 'widget';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * Where this display field description applies: user, agent
	 *
	 * @var string
	 * @orm:Column(name="display_zone", type="string", length=50)
	 */
	protected $display_zone;

	/**
	 * The type of elemenet: ticket_field, widget
	 *
	 * @var string
	 * @orm:Column(name="element_type", type="string", length=50)
	 */
	protected $element_type;

	/**
	 * The ID of the element
	 *
	 * @var string
	 * @orm:Column(name="element_id", type="integer")
	 */
	protected $element_id = 0;

	/**
	 * The initial state of the element. When the conditions fail,
	 * this state is reversed.
	 *
	 * @var string
	 * @orm:Column(name="initial_state", type="string", length=50)
	 */
	protected $initial_state = 'visible';

	/**
	 * An array of checks to run to see if the element should display.
	 * All of these must match.
	 *
	 * @var array
	 * @orm:Column(name="conds_all", type="array")
	 */
	protected $conds_all = array();

	/**
	 * An array of checks to run to see if the element should display.
	 * Any one of these must match.
	 *
	 * @var array
	 * @orm:Column(name="conds_any", type="array")
	 */
	protected $conds_any = array();


	/**
	 * @var int
	 * @orm:Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	public function getDepartmentId()
	{
		if ($this->department) {
			return $this->department['id'];
		}

		return 0;
	}

	/* todo need similar static php term matchign against people as we do for tickets */
}