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

/**
 * An email gateway contains info about how to read emails from an email account.
 * 
 * @orm:Entity
 * @orm:Table(name="email_gateways")
 */
class EmailGateway extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The name of the account. Eg the email address
	 *
	 * @var string
	 * @orm:Column(name="name", type="text", length=100)
	 */
	protected $name = '';

	/**
	 * The connection class that handles connecting/downloading etc.
	 *
	 * @var string
	 * @orm:Column(name="connection_class", type="string", length=80)
	 */
	protected $connection_class;

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
}