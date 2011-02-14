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
 * A client message is somethign we send to the browser.
 *
 * All messages are stored here, even if a user has a socket connection.
 * Message contents are rendered by the handlers, and the content differs
 * depending on the context (socket, ajax poll, mobile push etc). See the Handlers
 * for information about that.
 *
 * The point for this is that in some cases, the event is important, but the data the event
 * might represent may not be important. Or in most cases, the client may want to fetch the data
 * for the event later.
 *
 * For example, if a given element is not currently loaded or in view,
 * then we may want to defer fetching information until later when the view is activated.
 * So in that case, the original context would just push an ID of this client message,
 * and the client would later request the full information as an HTTP request or by pushing
 * the ID through the socket.
 *
 * @orm:HasLifecycleCallbacks
 * @orm:Entity
 * @orm:Table(name="client_messages", indexes={
 *     @orm:Index(name="date_created", columns={"date_created", "private_channel_id"})
 * })
 */
class ClientMessage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

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
	 * The auth is used when a client wants to fetch a "full" answer, if the
	 * original push sent only a short.
	 *
	 * @var string
	 * @orm:Column(name="auth", type="string", length=15)
	 */
	protected $auth;

	/**
	 * @var string
	 * @orm:Column(name="handler_class", type="string", length=15)
	 */
	protected $handler_class;

	/**
	 * Data to give the handler
	 *
	 * @var array
	 * @orm:Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * Event manager. This is used in the postInsert callback to notify
	 * any listeners. For example, if the web socket server is enabled,
	 * it'll listen to this even and can handle pushing the message through
	 * to clients.
	 *
	 * @var Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $event_dispatcher = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->auth = Strings::random(15, Strings::CHARS_KEY);

		if (App::has('event_dispatcher')) {
			$this->event_dispatcher = App::get('event_dispatcher');
		}
	}



	/**
	 * @param  $delivery_method
	 * @return void
	 */
	public function getHandler()
	{
		$handler_class = $this->handler_class;
		$handler = new $handler_class($this->data);

		return $handler;
	}


	/**
	 * @orm:postInsert
	 */
	public function notifyMessageServers()
	{
		if (!$this->event_dispatcher) return;

		$event = new \Symfony\Component\EventDispatcher\Event($this, 'deskpro.client_message.new');
		$this->event_dispatcher->notify($event);
	}
}