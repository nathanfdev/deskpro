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
 * Logs for email gateways.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="email_gateway_logs",
 *     indexes={@Index(name="log_type_idx", columns={"log_type"})}
 * )
 */
class EmailGatewayLog extends CustomDefAbstract
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="gateway_id", type="integer")
	 */
	protected $gateway_id;

	/**
	 * @var \Application\CoreBundle\Entity\EmailGateway
	 * @OneToOne(targetEntity="EmailGateway")
	 * @JoinColumn(name="gateway_id", referencedColumnName="id")
	 */
	protected $gateway = null;

	/**
	 * The log message
	 *
	 * @var string
	 * @Column(name="message", type="string", length=1000)
	 */
	protected $message;

	/**
	 * Other data, such as backtrace or debug info
	 *
	 * @var string
	 * @Column(name="data", type="array")
	 */
	protected $data;

	/**
	 * The log type
	 *
	 * @var string
	 * @Column(name="log_type", type="string", length=50)
	 */
	protected $log_type = 'info';

	/**
	 * The date the user was inserted into the system
	 *
	 * @var \DateTime
	 * @Column(name="date_created",type="datetime")
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