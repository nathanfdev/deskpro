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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * General logs.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="error_logs",
 *     indexes={@ORM_Mapping\Index(name="log_type_idx", columns={"log_type"})}
 * )
 */
class ErrorLog extends CustomDefAbstract
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * The log message
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="message", type="string", length=1000)
	 */
	protected $message;

	/**
	 * Other data, such as backtrace or debug info
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data;

	/**
	 * The log type
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="log_type", type="string", length=50)
	 */
	protected $log_type = 'info';

	/**
	 * The date the user was inserted into the system
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/** @ORM_Mapping\PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}