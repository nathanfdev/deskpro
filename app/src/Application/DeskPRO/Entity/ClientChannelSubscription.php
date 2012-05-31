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
 */
class ClientChannelSubscription extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * The session the subscription is for.
	 *
	 * @var Application\DeskPRO\Entity\Session
	 */
	protected $session;

	/**
	 * The channel the message is placed in.
	 *
	 * @var string
	 */
	protected $channel;

	/**
	 * A private channel. This is generally 'person:id' or 'session:id'. It is up
	 * to the message server implementation to enforce this auth requirement.
	 * (Ex in the ajax script we can just check the currently logged in user).
	 *
	 * @var string
	 */
	protected $private_channel_id = null;

	/**
	 * @var \DateTime
	 */
	protected $date_ping;


	public function __construct()
	{
		$this->date_ping    = new \DateTime();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
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
		$this->setModelField('date_ping', new \DateTime());
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ClientChannelSubscription';
		$metadata->setPrimaryTable(array( 'name' => 'client_channel_subscriptions', 'indexes' => array( 'date_ping' => array( 'columns' => array( 0 => 'date_ping', ), ), 'channel' => array( 'columns' => array( 0 => 'channel', ), ), ), ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'channel', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'channel', ));
		$metadata->mapField(array( 'fieldName' => 'private_channel_id', 'type' => 'string', 'length' => 150, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'private_channel_id', ));
		$metadata->mapField(array( 'fieldName' => 'date_ping', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_ping', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'session', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Session', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'session_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ),  ));
	}
}
