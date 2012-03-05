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
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Twitter Status
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TwitterStatus")
 * @ORM_Mapping\Table(name="twitter_statuses")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterStatus extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\GeneratedValue(strategy="NONE")
	 * @ORM_Mapping\Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterUser", inversedBy="statuses")
	 * @ORM_Mapping\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="text", type="string", length=4000)
	 */
	protected $text;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterStatus", inversedBy="replies")
	 * @ORM_Mapping\JoinColumn(name="in_reply_to_status_id", referencedColumnName="id", nullable=true)
	 */
	protected $in_reply_to_status;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TwitterStatus", mappedBy="in_reply_to_status")
	 */
	protected $replies;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterStatus", inversedBy="retweets")
	 * @ORM_Mapping\JoinColumn(name="retweet_id", referencedColumnName="id", nullable=true)
	 */
	protected $retweet;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TwitterStatus", mappedBy="retweet")
	 */
	protected $retweets;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterUser", inversedBy="replies")
	 * @ORM_Mapping\JoinColumn(name="in_reply_to_user_id", referencedColumnName="id", nullable=true)
	 */
	protected $in_reply_to_user;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterUser", inversedBy="messages")
	 * @ORM_Mapping\JoinColumn(name="recipient_id", referencedColumnName="id", nullable=true)
	 */
	protected $recipient;

	/**
	 * @var Boolean
	 * @ORM_Mapping\Column(name="is_truncated", type="boolean")
	 */
	protected $is_truncated = false;

	/**
	 * @var Boolean
	 * @ORM_Mapping\Column(name="is_favorited", type="boolean")
	 */
	protected $is_favorited = false;

	/**
	 * @var Boolean
	 * @ORM_Mapping\Column(name="is_archived", type="boolean")
	 */
	protected $is_archived = false;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	/**
	 * @var double
	 * @ORM_Mapping\Column(name="geo_latitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_latitude;

	/**
	 * @var double
	 * @ORM_Mapping\Column(name="geo_longitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_longitude;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="source", type="string", length=4000, nullable=true)
	 */
	protected $source;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $agent = null;

	/**
	 * @var \Application\DeskPRO\Entity\AgentTeam
	 * @ORM_Mapping\ManyToOne(targetEntity="AgentTeam", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $agent_team = null;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatusLong
	 * @ORM_Mapping\OneToOne(targetEntity="TwitterStatusLong", mappedBy="status")
	 */
	protected $long;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TwitterStatusMention", mappedBy="status")
	 */
	protected $mentions;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TwitterStatusTag", mappedBy="status")
	 */
	protected $tags;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TwitterStatusUrl", mappedBy="status")
	 */
	protected $urls;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TwitterStatusNote", mappedBy="status")
	 */
	protected $notes;

	/**
	 * @var string
	 */
	protected $_parsed_text;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->replies = new \Doctrine\Common\Collections\ArrayCollection();
		$this->retweets = new \Doctrine\Common\Collections\ArrayCollection();

		$this->mentions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		$this->urls = new \Doctrine\Common\Collections\ArrayCollection();

		$this->notes = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @return integer
	 */
	public function getUserId()
	{
		if (null !== $this->user) {
			return $this->user->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setUserId($id)
	{
		if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
			$this->user = $user;
		} else {
			$this->user = null;
		}
	}

	/**
	 * @return Boolean
	 */
	protected function hasLongVersion()
	{
		return null !== $this->long_version;
	}

	/**
	 * @return Boolean
	 */
	public function isMessage()
	{
		return null !== $this->recipient;
	}

	/**
	 * @return Boolean
	 */
	public function isReply()
	{
		return null !== $this->in_reply_to_status || null !== $this->in_reply_to_user;
	}

	/**
	 * @return integer
	 */
	public function getInReplyToStatusId()
	{
		if (null !== $this->in_reply_to_status) {
			return $this->in_reply_to_status->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setInReplyToStatusId($id)
	{
		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->in_reply_to_status = $status;
		} else {
			$this->in_reply_to_status = null;
		}
	}

	/**
	 * @return integer
	 */
	public function getRetweetId()
	{
		if (null !== $this->retweet) {
			return $this->retweet->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setRetweetId($id)
	{
		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->retweet = $status;
		} else {
			$this->retweet = null;
		}
	}

	/**
	 * @return Boolean
	 */
	public function isRetweet()
	{
		return null !== $this->retweet;
	}

	/**
	 * @return integer
	 */
	public function getInReplyToUserId()
	{
		if (null !== $this->in_reply_to_user) {
			return $this->in_reply_to_user->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setInReplyToUserId($id)
	{
		if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
			$this->in_reply_to_user = $user;
		} else {
			$this->in_reply_to_user = null;
		}
	}

	/**
	 * @return integer
	 */
	public function getRecipientId()
	{
		if (null !== $this->recipient) {
			return $this->recipient->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setRecipientId($id)
	{
		if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
			$this->recipient = $user;
		} else {
			$this->recipient = null;
		}
	}

	/**
	 * @return Boolean
	 */
	public function isTruncated()
	{
		return (Boolean) $this->is_truncated;
	}

	/**
	 * @return Boolean
	 */
	public function isFavorited()
	{
		return (Boolean) $this->is_favorited;
	}

	/**
	 * @return Boolean
	 */
	public function isArchived()
	{
		return (Boolean) $this->is_archived;
	}

	/**
	 * Retrieve a parsed version of status' text.
	 *
	 * @return string
	 */
	public function getParsedText()
	{
		if (null !== $this->_parsed_text) {
			return $this->_parsed_text;
		}

		$replacements = array();
		foreach ($this['mentions'] as $mention) {
			$replacements[$mention['starts']] = $mention;
		}
		foreach ($this['tags'] as $tag) {
			$replacements[$tag['starts']] = $tag;
		}
		foreach ($this['urls'] as $url) {
			$replacements[$url['starts']] = $url;
		}

		if (!count($replacements)) {
			$this->_parsed_text = $this['text'];
			return $this->_parsed_text;
		}

		ksort($replacements);

		$cursor = 0;
		$this->_parsed_text = '';
		foreach ($replacements as $starts => $replacement) {
			$this->_parsed_text .= substr($this['text'], $cursor, $starts - $cursor);
			$replace = substr($this['text'], $starts, $replacement['ends'] - $starts);
			$cursor = $replacement['ends'];

			switch (get_class($replacement)) {
				case 'Application\\DeskPRO\\Entity\\TwitterStatusMention':
					$this->_parsed_text .= sprintf('<a class="mention" data-user-id="%s">@%s</a>', $replacement['user']['id'], htmlspecialchars($replacement['user']['screen_name']));
					break;
				case 'Application\\DeskPRO\\Entity\\TwitterStatusTag':
					$this->_parsed_text .= sprintf('<a class="hash" data-hash="%1$s">#%1$s</a>', htmlspecialchars($replacement['hash']));
					break;
				case 'Application\\DeskPRO\\Entity\\TwitterStatusUrl':
					$this->_parsed_text .= sprintf('<a class="url" href="%s" target="_twitter_url_%s">%s</a>', htmlspecialchars($replacement['url']), md5($replacement['id']), $replace);
					break;
				default:
					$this->_parsed_text .= $replace;
					break;
			}
		}

		if (strlen($this['text']) != $cursor) {
			$this->_parsed_text .= substr($this['text'], $cursor);
		}

		return $this->_parsed_text;
	}

	public function getClippedParsedText($length = null)
	{
		$parsedText = $this->getParsedText();

		if (true === is_null($length)) {
			return $parsedText;
		}
		else {
			return substr($parsedText, 0, $length);
		}

	}

	/**
	 * @param \SimpleXMLElement|\Zend\Rest\Client\Result $status
	 * @return \Application\DeskPRO\Entity\TwitterStatus
	 */
	static public function createFromXML($status)
	{
		// @!TODO check against \SimpleXMLElement & \Zend\Rest\Client\Result

		$entity                 = new self();
		$entity['id']           = (string) $status->id;
		$entity['text']         = (string) $status->text;
		$entity['is_truncated'] = (Boolean) (integer) $status->truncated;
		$entity['is_favorited'] = (Boolean) (integer) $status->favorited;
		$entity['is_archived']  = false;
		$entity['date_created'] = new \DateTime((string) $status->created_at);
		$entity['source']       = (string) $status->source;

		// @!TODO add geo informations
		// $entity['geo_latitude'] = (float) $status['geo'][];
		// $entity['geo_longitude'] = (float) $status['geo'][];

		return $entity;
	}

	/**
	 * @param array $status
	 * @return \Application\DeskPRO\Entity\TwitterStatus
	 */
	static public function createFromJson(array $status)
	{
		$entity                 = new self();
		$entity['id']           = $status['id_str'];
		$entity['text']         = $status['text'];
		$entity['is_truncated'] = $status['truncated'];
		$entity['is_favorited'] = $status['favorited'];
		$entity['is_archived']  = false;
		$entity['date_created'] = new \DateTime($status['created_at']);
		$entity['source']       = $status['source'];

		// @!TODO add geo informations
		// $entity['geo_latitude'] = $json['geo'][];
		// $entity['geo_longitude'] = $json['geo'][];

		return $entity;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TwitterStatus'; 
		$metadata->setPrimaryTable(array( 'name' => 'twitter_statuses', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'bigint', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'text', 'type' => 'string', 'length' => 4000, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'text', )); 
		$metadata->mapField(array( 'fieldName' => 'is_truncated', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_truncated', )); 
		$metadata->mapField(array( 'fieldName' => 'is_favorited', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_favorited', )); 
		$metadata->mapField(array( 'fieldName' => 'is_archived', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_archived', )); 
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'date_created', )); 
		$metadata->mapField(array( 'fieldName' => 'geo_latitude', 'type' => 'decimal', 'length' => NULL, 'precision' => 10, 'scale' => 5, 'nullable' => true, 'unique' => false, 'columnName' => 'geo_latitude', )); 
		$metadata->mapField(array( 'fieldName' => 'geo_longitude', 'type' => 'decimal', 'length' => NULL, 'precision' => 10, 'scale' => 5, 'nullable' => true, 'unique' => false, 'columnName' => 'geo_longitude', )); 
		$metadata->mapField(array( 'fieldName' => 'source', 'type' => 'string', 'length' => 4000, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'source', )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'user', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterUser', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'statuses', 'joinColumns' => array( 0 => array( 'name' => 'user_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'in_reply_to_status', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'replies', 'joinColumns' => array( 0 => array( 'name' => 'in_reply_to_status_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'replies', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'cascade' => array( ), 'mappedBy' => 'in_reply_to_status', 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'retweet', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'retweets', 'joinColumns' => array( 0 => array( 'name' => 'retweet_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'retweets', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'cascade' => array( ), 'mappedBy' => 'retweet', 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'in_reply_to_user', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterUser', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'replies', 'joinColumns' => array( 0 => array( 'name' => 'in_reply_to_user_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'recipient', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterUser', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'messages', 'joinColumns' => array( 0 => array( 'name' => 'recipient_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'agent', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'agent_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'agent_team', 'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentTeam', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'agent_team_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'long', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatusLong', 'cascade' => array( ), 'mappedBy' => 'status', 'inversedBy' => NULL, 'joinColumns' => array( ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'mentions', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatusMention', 'cascade' => array( ), 'mappedBy' => 'status', 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'tags', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatusTag', 'cascade' => array( ), 'mappedBy' => 'status', 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'urls', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatusUrl', 'cascade' => array( ), 'mappedBy' => 'status', 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'notes', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatusNote', 'cascade' => array( ), 'mappedBy' => 'status', 'orphanRemoval' => false, ));
	}
}

