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

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;

/**
 * An email gateway contains info about how to read emails from an email account.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\EmailGateway")
 * @orm:Table(name="email_gateways")
 */
class EmailGateway extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The human name of the account.
	 *
	 * @var string
	 * @orm:Column(name="name", type="text", length=100)
	 */
	protected $name = '';

	/**
	 * The email address for the account.
	 *
	 * @var string
	 * @orm:Column(name="address", type="text", length=255)
	 */
	protected $address;

	/**
	 * The connection class that handles connecting/downloading etc.
	 *
	 * @var string
	 * @orm:Column(name="connection_class", type="string", length=80)
	 */
	protected $connection_class = '';

	/**
	 * Options for the connection handler
	 *
	 * @orm:Column(name="connection_options", type="array")
	 */
	protected $connection_options = array();

	/**
	 * The class that processes the email. For example, into tickets
	 * or agent replies etc.
	 *
	 * @var string
	 * @orm:Column(name="processor_class", type="string", length=80)
	 */
	protected $processor_class;

	/**
	 * @var bool
	 * @orm:Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * The last time this gateway successfully connected and checked for messages.
	 *
	 * @var \DateTime
	 * @orm:Column(name="date_last_login", type="datetime", nullable=true)
	 */
	protected $date_last_login = null;


	
	/**
	 * Get a new instance of the processor class for an email
	 *
	 * @param \Application\DeskPRO\EmailGateway\Reader\AbstractReader $reader
	 * @return \Application\DeskPRO\EmailGateway\AbstractGateway
	 */
	public function getNewProcessor(AbstractReader $reader)
	{
		$proc = new $this->processor_class($this, $reader);
		return $proc;
	}
}