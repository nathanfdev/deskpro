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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

/**
 * An email gateway contains info about how to read emails from an email account.
 *
 * @property int $id
 * @property string $connection_type
 * @property array $connection_options
 * @property string $gateway_type
 * @property bool $is_enabled
 * @property \DateTime|null $start_date_limit
 * @property bool $keep_read
 * @property EmailGatewayAddress $primary_address
 * @property EmailGatewayAddress[] $addresses
 * @property \DateTime|null $date_last_check
 * @property EmailTransport $linked_transport
 * @property array|null $processor_extras
 */
class EmailGateway extends \Application\DeskPRO\Domain\DomainObject
{
	const CONN_POP3    = 'pop3';
	const CONN_GMAIL   = 'gmail';
	const CONN_IMAP    = 'imap';
	const CONN_READDIR = 'directory';

	const GATEWAY_TICKETS = 'tickets';
	const GATEWAY_ARTICLES = 'articles';

	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * The type of connection this class represents
	 *
	 * @var string
	 */
	protected $connection_type = '';

	/**
	 * Options for the connection handler
	 *
	 */
	protected $connection_options = array();

	/**
	 * The type of gateway this is. For example, it processes tickets and ticket replies.
	 *
	 * @var string
	 */
	protected $gateway_type;

	/**
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * @var null
	 */
	protected $start_date_limit = null;

	/**
	 * @var bool
	 */
	protected $keep_read = false;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGatewayAddress
	 */
	protected $primary_address;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $addresses;

	/**
	 * The last time this gateway successfully connected and checked for messages.
	 *
	 * @var \DateTime
	 */
	protected $date_last_check = null;

	/**
	 * @var \Application\DeskPRO\Entity\EmailTransport
	 */
	protected $linked_transport;

	/**
	 * @var array
	 */
	protected $processor_extras = null;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher
	 */
	protected $_fetcher = null;

	/**
	 * @return EmailGateway
	 */
	public static function createTicketAccount()
	{
		$acc = new self(self::GATEWAY_TICKETS);
		return $acc;
	}


	/**
	 * @param string $gateway_type
	 */
	public function __construct($gateway_type)
	{
		$this->addresses = new ArrayCollection();
		$this->gateway_type = $gateway_type;
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		$addr = $this->getPrimaryEmailAddress(false);
		if ($addr) {
			return $addr;
		}

		if ($this->id) {
			return "EmailGateway:{$this->id}";
		}

		return "EmailGateway:new";
	}


	/**
	 * Get the primary email address on this gateway
	 *
	 * @param bool $as_obj
	 * @return string
	 */
	public function getPrimaryEmailAddress($as_obj = false)
	{
		if (!$this->addresses) {
			return null;
		}

		$addr = $this->addresses->first();
		if ($addr && $as_obj) {
			return $addr;
		}

		if (!$addr || $addr->match_type != 'exact') {
			return null;
		}

		return $addr->match_pattern;
	}


	/**
	 * Get an email alias. By convention this means the second email address, the first is considered the primary.
	 *
	 * @param bool $as_obj
	 * @return string
	 */
	public function getAliasEmailAddress($as_obj = false)
	{
		if (!$this->addresses || !$this->addresses->containsKey(1)) {
			return null;
		}

		$addr = $this->addresses->get(1);
		if ($addr && $as_obj) {
			return $addr;
		}

		if (!$addr || $addr->match_type != 'exact') {
			return null;
		}

		return $addr->match_pattern;
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

			case self::GATEWAY_ARTICLES:
				$proc = new \Application\DeskPRO\EmailGateway\ArticleGatewayProcessor($this, $reader, $options);
				break;

			default:
				throw new \InvalidArgumentException("Invalid gateway type `{$this->gateway_type}`");
		}

		return $proc;
	}


	/**
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getSourceObjectType()
	{
		switch ($this->gateway_type) {
			case self::GATEWAY_TICKETS: return 'ticket';
			case self::GATEWAY_ARTICLES: return 'article';

			default:
				throw new \InvalidArgumentException("Invalid gateway type `{$this->gateway_type}`");
		}
	}


	/**
	 * @param string $name
	 * @param mixed  $default
	 * @return mixed
	 */
	public function getProcessorExtra($name, $default = null)
	{
		if (is_array($this->processor_extras) && array_key_exists($name, $this->processor_extras)) {
			return $this->processor_extras[$name];
		} else {
			return $default;
		}
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function setProcessorExtra($name, $value)
	{
		if (!is_array($this->processor_extras)) {
			$this->processor_extras = array();
		}

		if (!array_key_exists($name, $this->processor_extras) || $this->processor_extras[$name] !== $value) {
			$old = $this->processor_extras;
			$this->processor_extras[$name] = $value;
			$this->_onPropertyChanged('processor_extras', $old, $this->processor_extras);
		}
	}


	/**
	 * Get an instance of the fetcher class
	 *
	 * @return \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher
	 * @throws \InvalidArgumentException
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

			case self::CONN_READDIR:
				$this->_fetcher = new \Application\DeskPRO\EmailGateway\Fetcher\PlainMailDir($this);
				break;

			default:
				throw new \InvalidArgumentException("Invalid connection type `{$this->connection_type}`");
		}

		return $this->_fetcher;
	}


	/**
	 * @return string
	 */
	public function getMatchPatternDisplay()
	{
		$patterns = array();

		foreach ($this->addresses as $adr) {
			$patterns[] = $adr->match_pattern;
		}

		return implode(', ', $patterns);
	}


	/**
	 * @param bool $primary
	 * @param bool $deep
	 * @param array $visited
	 * @return array
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);

		if ($deep) {
			$data['linked_transport'] = null;
			if ($this->linked_transport) {
				$data['linked_transport'] = $this->linked_transport->toApiData(false, false);
			}

			$data['addresses'] = array();
			if ($this->addresses) {
				foreach ($this->addresses as $addr) {
					$data['addresses'][] = $addr->toApiData(false, false);
				}
			}
		}

		return $data;
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\EmailGateway';
		$metadata->setPrimaryTable(array( 'name' => 'email_gateways', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'connection_type', 'type' => 'string', 'length' => 15, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'connection_type', ));
		$metadata->mapField(array( 'fieldName' => 'connection_options', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'connection_options', ));
		$metadata->mapField(array( 'fieldName' => 'gateway_type', 'type' => 'string', 'length' => 15, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'gateway_type', ));
		$metadata->mapField(array( 'fieldName' => 'is_enabled', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_enabled', ));
		$metadata->mapField(array( 'fieldName' => 'start_date_limit', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'start_date_limit', ));
		$metadata->mapField(array( 'fieldName' => 'keep_read', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'keep_read', ));
		$metadata->mapField(array( 'fieldName' => 'date_last_check', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'date_last_check', ));
		$metadata->mapField(array( 'fieldName' => 'processor_extras', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'processor_extras', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'primary_address', 'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailGatewayAddress', 'mappedBy' => NULL, 'inversedBy' => NULL, 'fetch' => ClassMetadata::FETCH_EAGER, 'joinColumns' => array( 0 => array( 'name' => 'primary_address_id', 'referencedColumnName' => 'id', 'unique' => true, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'addresses', 'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailGatewayAddress', 'cascade' => array( 0 => 'remove', 1 => 'persist', 3 => 'merge', ), 'mappedBy' => 'gateway', 'orderBy' => array('run_order' => 'ASC') ));
		$metadata->mapManyToOne(array( 'fieldName' => 'linked_transport', 'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailTransport', 'cascade' => array('persist'), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'linked_transport_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'SET NULL', 'columnDefinition' => NULL, ), ),  ));
	}
}
