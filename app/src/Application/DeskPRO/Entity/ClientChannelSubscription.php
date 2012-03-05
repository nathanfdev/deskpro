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

/**
 * A subscription is a record of a channel a client is currently listening to.
 * We use this list to make sure messages are generated and sent to clients about
 * the things they care about.
 *
 * The client continuously verifies the list of subscriptions and they expire after a
 * time (for example, if the client disconnects without letting us know).
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ClientChannelSubscription")
 * @ORM_Mapping\Table(name="client_channel_subscriptions", indexes={
 *     @ORM_Mapping\Index(name="date_ping", columns={"date_ping"}),
 *     @ORM_Mapping\Index(name="channel", columns={"channel"})
 * })
 */
class ClientChannelSubscription extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The session the subscription is for.
	 *
	 * @var Application\DeskPRO\Entity\Session
	 * @ORM_Mapping\ManyToOne(targetEntity="Session")
	 * @ORM_Mapping\JoinColumn(name="session_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $session;

	/**
	 * The channel the message is placed in.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="channel", type="string", length=255)
	 */
	protected $channel;

	/**
	 * A private channel. This is generally 'person:id' or 'session:id'. It is up
	 * to the message server implementation to enforce this auth requirement.
	 * (Ex in the ajax script we can just check the currently logged in user).
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="private_channel_id", type="string", length=150, nullable=true)
	 */
	protected $private_channel_id = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_ping",type="datetime")
	 */
	protected $date_ping;


	public function __construct()
	{
		$this->date_ping    = new \DateTime();
	}

	public function setSessionId($session_id)
	{
		if ($session_id) {
			$this['session'] = App::getEntityRepository('DeskPRO:Session')->find($session_id);
		} else {
			$this['session'] = null;
		}
	}



	public function updatePingTime()
	{
		$this->date_ping = new \DateTime();
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ClientChannelSubscription'; 
		$metadata->setPrimaryTable(array( 'name' => 'client_channel_subscriptions', 'indexes' => array( 'date_ping' => array( 'columns' => array( 0 => 'date_ping', ), ), 'channel' => array( 'columns' => array( 0 => 'channel', ), ), ), )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'channel', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'channel', )); 
		$metadata->mapField(array( 'fieldName' => 'private_channel_id', 'type' => 'string', 'length' => 150, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'private_channel_id', )); 
		$metadata->mapField(array( 'fieldName' => 'date_ping', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'date_ping', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY); 
		$metadata->mapOneToOne(array( 'fieldName' => 'session', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Session', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'session_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, ));
	}
}

