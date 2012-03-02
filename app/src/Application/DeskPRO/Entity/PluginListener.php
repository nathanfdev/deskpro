<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * These are callbacks that are fired at specific events or points in the code.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="plugin_listeners")
 */
class PluginListener extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Plugin
	 * @ORM_Mapping\ManyToOne(targetEntity="Plugin", inversedBy="plugins")
	 * @ORM_Mapping\JoinColumn(name="plugin_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $plugin;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="event_name", type="string", length=255, nullable=true)
	 */
	protected $event_name = null;

	/**
	 * Options for event listener connector. Sometimes an event listener can decide which
	 * plugins to instantiate. For example, if a plugin needs to plug into a specific
	 * field.
	 *
	 * @ORM_Mapping\Column(name="event_options", type="array")
	 */
	protected $event_options = array();

	/**
	 * The description of what this plugin does
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="description", type="string", length=255)
	 */
	protected $description = '';

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="run_order", type="integer")
	 */
	protected $run_order = 0;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="listener_class", type="string", length=255)
	 */
	protected $listener_class;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;


	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}
