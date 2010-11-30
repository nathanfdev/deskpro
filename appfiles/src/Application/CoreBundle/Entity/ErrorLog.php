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
 * General logs.
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="error_logs",
 *     indexes={@orm:Index(name="log_type_idx", columns={"log_type"})}
 * )
 */
class ErrorLog extends CustomDefAbstract
{
	/**
	 * @var int
	 * @orm:Id @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

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
	 * @orm:Column(name="data", type="array")
	 */
	protected $data;

	/**
	 * The log type
	 *
	 * @var string
	 * @orm:Column(name="log_type", type="string", length=50)
	 */
	protected $log_type = 'info';

	/**
	 * The date the user was inserted into the system
	 *
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/** @PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}