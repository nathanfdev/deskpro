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

use \Application\DeskPRO\App;

/**
 * A subscription is a record of a channel a client is currently listening to.
 * We use this list to make sure messages are generated and sent to clients about
 * the things they care about.
 *
 * The client continuously verifies the list of subscriptions and they expire after a
 * time (for example, if the client disconnects without letting us know).
 *
 * @orm:Entity
 * @orm:Table(name="client_message_subscriptions", indexes={
 *     @orm:Index(name="date_ping", columns={"date_ping"}),
 *     @orm:Index(name="channel", columns={"channel"})
 * })
 */
class ClientChannelSubscription extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The session the subscription is for.
	 *
	 * @var Application\DeskPRO\Entity\Session
	 * @orm:ManyToOne(targetEntity="Session")
	 * @orm:JoinColumn(name="session_id", referencedColumnName="id")
	 */
	protected $session;

	/**
	 * The channel the message is placed in.
	 *
	 * @var string
	 * @orm:Column(name="channel", type="string", length=255)
	 */
	protected $channel;

	/**
	 * A private channel. This is generally 'person:id' or 'session:id'. It is up
	 * to the message server implementation to enforce this auth requirement.
	 * (Ex in the ajax script we can just check the currently logged in user).
	 *
	 * @var string
	 * @orm:Column(name="private_channel_id", type="string", length="150", nullable=true)
	 */
	protected $private_channel_id = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_ping",type="datetime")
	 */
	protected $date_ping;


	public function __construct()
	{
		$this->date_ping    = new \DateTime();
		$this->auth = Strings::random(15, Strings::CHARS_KEY);
	}



	public function updatePingTime()
	{
		$this->date_ping = new \DateTime();
	}
}
