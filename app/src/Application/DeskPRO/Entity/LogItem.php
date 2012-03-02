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
 * General logs
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\LogItem")
 * @ORM_Mapping\Table(name="log_items",
 *     indexes={
 *         @ORM_Mapping\Index(name="log_name_idx", columns={"log_name","session_name"}),
 *         @ORM_Mapping\Index(name="flag_idx", columns={"flag"})
 *     }
 * )
 */
class LogItem extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The log group, or "file". Different types of logs can be in different groups for
	 * each kind of component (eg. gateways, error_log, etc).
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="log_name", type="string", length=50)
	 */
	protected $log_name;

	/**
	 * A log 'session'. A way to group many log items together as part of a whole
	 * procedure.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="session_name", type="string", length=100, nullable=true)
	 */
	protected $session_name = null;

	/**
	 * Any kind of special flag to mark this log item.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="flag", type="string", length=50, nullable=true)
	 */
	protected $flag = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="priority", type="integer")
	 */
	protected $priority;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="priority_name", type="string", length=25)
	 */
	protected $priority_name;

	/**
	 * The log message
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="message", type="text")
	 */
	protected $message;

	/**
	 * Other data, such as backtrace or debug info
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="array", nullable=true)
	 */
	protected $data = null;

	/**
	 * The date the user was inserted into the system
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}
