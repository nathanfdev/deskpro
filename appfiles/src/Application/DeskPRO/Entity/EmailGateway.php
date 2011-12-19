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

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

/**
 * An email gateway contains info about how to read emails from an email account.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\EmailGateway")
 * @ORM_Mapping\Table(name="email_gateways")
 */
class EmailGateway extends \Application\DeskPRO\Domain\DomainObject
{
	const CONN_POP3  = 'pop3';
	const CONN_GMAIL = 'gmail';
	const CONN_IMAP  = 'imap';

	const GATEWAY_TICKETS = 'tickets';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The human name of the account.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="text", length=100)
	 */
	protected $title = '';

	/**
	 * The type of connection this class represents
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="connection_type", type="string", length=15)
	 */
	protected $connection_type = '';

	/**
	 * Options for the connection handler
	 *
	 * @ORM_Mapping\Column(name="connection_options", type="array")
	 */
	protected $connection_options = array();

	/**
	 * The type of gateway this is. For example, it processes tickets and ticket replies.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="gateway_type", type="string", length=15)
	 */
	protected $gateway_type;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGatewayAddress
	 * @ORM_Mapping\ManyToOne(targetEntity="EmailGatewayAddress", inversedBy="default_address")
	 * @ORM_Mapping\JoinColumn(name="default_gateway_address_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $default_address;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="EmailGatewayAddress", mappedBy="gateway", cascade={"persist", "remove", "merge"}, indexBy="id")
	 */
	protected $addresses;

	/**
	 * The last time this gateway successfully connected and checked for messages.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_last_check", type="datetime", nullable=true)
	 */
	protected $date_last_check = null;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher
	 */
	protected $_fetcher = null;

	public function __construct()
	{
		$this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * @return array
	 */
	public function getConnectionOptions()
	{
		if ($this->connection_type != 'gmail') {
			return $this->connection_options;
		}

		$options = $this->connection_options;
		$options['host'] = 'pop.gmail.com';
		$options['secure'] = 'ssl';
		$options['port'] = '995';

		return $options;
	}


	/**
	 * Get a new instance of the processor class for an email
	 *
	 * @param \Application\DeskPRO\EmailGateway\Reader\AbstractReader $reader
	 * @return \Application\DeskPRO\EmailGateway\AbstractGatewayProcessor
	 */
	public function getNewProcessor(AbstractReader $reader, array $options = array())
	{
		switch ($this->gateway_type) {
			case self::GATEWAY_TICKETS:
				$proc = new \Application\DeskPRO\EmailGateway\TicketGatewayProcessor($this, $reader, $options);
				break;

			default:
				throw new \InvalidArgumentException("Invalid gateway type `{$this->gateway_type}`");
		}

		return $proc;
	}


	/**
	 * Get an instance of the fetcher class
	 *
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher
	 */
	public function getFetcher()
	{
		if ($this->_fetcher !== null) return $this->_fetcher;

		switch ($this->connection_type) {
			case self::CONN_POP3:
			case self::CONN_GMAIL:
				$this->_fetcher = new \Application\DeskPRO\EmailGateway\Fetcher\Pop3($this);
				break;

			case self::CONN_IMAP:
				$this->_fetcher = new \Application\DeskPRO\EmailGateway\Fetcher\Imap($this);
				break;

			default:
				throw new \InvalidArgumentException("Invalid connection type `{$this->connection_type}`");
		}

		return $this->_fetcher;
	}
}
