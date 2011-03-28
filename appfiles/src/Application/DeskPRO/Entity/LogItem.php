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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * General logs
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\LogItem")
 * @orm:Table(name="log_items",
 *     indexes={
 *         @orm:Index(name="log_name_idx", columns={"log_name","session_name"}),
 *         @orm:Index(name="flag_idx", columns={"flag"})
 *     }
 * )
 */
class LogItem extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The log group, or "file". Different types of logs can be in different groups for
	 * each kind of component (eg. gateways, error_log, etc).
	 *
	 * @var string
	 * @orm:Column(name="log_name", type="string", length=50)
	 */
	protected $log_name;

	/**
	 * A log 'session'. A way to group many log items together as part of a whole
	 * procedure.
	 *
	 * @var string
	 * @orm:Column(name="session_name", type="string", length=100, nullable=true)
	 */
	protected $session_name = null;

	/**
	 * Any kind of special flag to mark this log item.
	 *
	 * @var string
	 * @orm:Column(name="flag", type="string", length=50, nullable=true)
	 */
	protected $flag = null;

	/**
	 * @var int
	 * @orm:Column(name="priority", type="integer")
	 */
	protected $priority;

	/**
	 * @var string
	 * @orm:Column(name="priority_name", type="string", length=25)
	 */
	protected $priority_name;

	/**
	 * The log message
	 *
	 * @var string
	 * @orm:Column(name="message", type="string", length=1000)
	 */
	protected $message;

	/**
	 * Other data, such as backtrace or debug info
	 *
	 * @var string
	 * @orm:Column(name="data", type="array", nullable=true)
	 */
	protected $data = null;

	/**
	 * The date the user was inserted into the system
	 *
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}