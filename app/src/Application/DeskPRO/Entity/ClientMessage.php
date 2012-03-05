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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;

use Orb\Util\Strings;

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
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ClientMessage")
 * @ORM_Mapping\Table(name="client_messages")
 */
class ClientMessage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The channel the message is placed in.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="channel", type="string", length=255)
	 */
	protected $channel;

	/**
	 * The auth is used when a client wants to fetch a "full" answer, if the
	 * original push sent only a short.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="auth", type="string", length=15)
	 */
	protected $auth;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="handler_class", type="string", length=255)
	 */
	protected $handler_class = 'Application\\DeskPRO\\ClientMessage\\MessageHandler\\BasicArray';

	/**
	 * Data to give the handler
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * The client ID (usully sessionid) that created this message.
	 * This is so when we fetch messages, we don't get our own messages back.
	 *
	 * @ORM_Mapping\Column(name="created_by_client", type="string", length=255)
	 */
	protected $created_by_client = '';

	/**
	 * The client ID (usully sessionid) that this message is for
	 * specifically.
	 *
	 * @ORM_Mapping\Column(name="for_client", type="string", length=255, nullable=true)
	 */
	protected $for_client;

	/**
	 * Who this message is for specifically
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="for_person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $for_person;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * Event manager. This is used in the PostPersist callback to notify
	 * any listeners. For example, if the web socket server is enabled,
	 * it'll listen to this even and can handle pushing the message through
	 * to clients.
	 *
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
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
		$handler = new $handler_class($this);

		return $handler;
	}


	/**
	 * @ORM_Mapping\PostPersist
	 */
	public function notifyMessageServers()
	{
		if (!$this->event_dispatcher) return;

		$event = new \Application\DeskPRO\ClientMessage\Event($this);

		$this->event_dispatcher->dispatch('DeskPRO_onNewClientMessage', $event);
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ClientMessage'; 
		$metadata->setPrimaryTable(array( 'name' => 'client_messages', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->addLifecycleCallback('notifyMessageServers', 'postPersist'); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'channel', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'channel', )); 
		$metadata->mapField(array( 'fieldName' => 'auth', 'type' => 'string', 'length' => 15, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'auth', )); 
		$metadata->mapField(array( 'fieldName' => 'handler_class', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'handler_class', )); 
		$metadata->mapField(array( 'fieldName' => 'data', 'type' => 'array', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'data', )); 
		$metadata->mapField(array( 'fieldName' => 'created_by_client', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'created_by_client', )); 
		$metadata->mapField(array( 'fieldName' => 'for_client', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'for_client', )); 
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'date_created', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY); 
		$metadata->mapOneToOne(array( 'fieldName' => 'for_person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'for_person_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, ));
	}
}

