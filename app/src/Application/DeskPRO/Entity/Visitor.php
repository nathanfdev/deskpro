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
use Orb\Util\Util;

/**
 * Visitor records try to identify a user long-term across many visits by
 * placing a cookie that doesn't expire. This can be used by some tracking,
 * but it's also used by things like anonymous voting to prevent repeat votes and such.
 *
 * A visitor is 1) Someone who has the correct visitor code or 2) someone who
 * is using the same IP address within 1 day with same browser useragent
 *
 * It's sortof like a session except its not used for anything dangerous like granting
 * access to things.
 */
class Visitor extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * The authcode to verify an id
	 *
	 * @var string
	 */
	protected $auth;

	/**
	 * @var \Application\DeskPRO\Entity\VisitorTrack
	 */
	protected $initial_track;

	/**
	 * @var \Application\DeskPRO\Entity\VisitorTrack
	 */
	protected $visit_track;

	/**
	 * @var \Application\DeskPRO\Entity\VisitorTrack
	 */
	protected $last_track;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person = null;

	/**
	 * @var int
	 */
	protected $page_count = 0;

	/**
	 * The users name. Sometimes we might ask the users name, so we can
	 * save it in the visitor record for future reference
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * The users email, like the name above
	 *
	 * @var string
	 */
	protected $email = '';

	/**
	 * @var array
	 */
	protected $chat_invite = null;

	/**
	 * @var \DateTime
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 */
	protected $date_last;

	/**
	 * @var \Application\DeskPRO\Entity\VisitorTrack
	 */
	protected $_set_last_track;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function __construct()
	{
		$this->is_new = true;
		$this->setModelField('auth', Strings::random(15, Strings::CHARS_KEY));
		$this->setModelField('date_created', new \DateTime());
		$this->setModelField('date_last', new \DateTime());
	}


	/**
	 * Gets the ID for this vis. It's an encoded ID and an authcode.
	 *
	 * @return string
	 */
	public function getVisitorCode()
	{
		return $this->id . '-' . $this->auth;
	}



	/**
	 * Check a vis code against some kind o finput to see
	 * if they match.
	 *
	 * @return bool
	 */
	public function checkVisitorCode($vis_code)
	{
		return ($this->getVisitorCode() === $vis_code);
	}


	/**
	 * @param string $vis_code
	 * @return int
	 */
	public static function getIdFromCode($vis_code)
	{
		if (!strpos($vis_code, '-')) return null;

		list ($vis_id, ) = explode('-', $vis_code, 2);

		return $vis_id;
	}


	/**
	 * A secret hash of this session key with the app secret.
	 *
	 * Most notably used as the "proxy key"
	 *
	 * @param  string $secret Another component to add to the hash
	 * @return string
	 */
	public function getVisitorSecret($name = '')
	{
		return md5($this->id . $this->auth . App::getAppSecret() . $name);
	}


	/**
	 * Generate a security token based off of this session
	 *
	 * @param $name
	 * @param int $timeout
	 * @return string
	 */
	public function generateSecurityToken($name, $timeout = 43200)
	{
		return Util::generateStaticSecurityToken($this->getVisitorSecret($name), $timeout);
	}


	/**
	 * Check a security token to see if its valid
	 *
	 * @param $name
	 * @return bool
	 */
	public function checkSecurityToken($name, $token)
	{
		return Util::checkStaticSecurityToken($token, $this->getVisitorSecret($name));
	}


	/**
	 * @return string
	 */
	public function getIpAddress()
	{
		if (!$this->last_track) {
			return null;
		}

		return $this->last_track->ip_address;
	}


	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		if (!$this->last_track) {
			return null;
		}

		return $this->last_track->user_agent;
	}


	/**
	 * @return string
	 */
	public function getLastPage()
	{
		if (!$this->last_track) {
			return null;
		}

		return $this->last_track->page_url;
	}


	/**
	 * @param VisitorTrack $track
	 */
	public function setLastTrack(VisitorTrack $track)
	{
		$this->setModelField('last_track', $track);
		$this->_set_last_track = $track;
	}


	/**
	 * @return VisitorTrack
	 */
	public function getSetLastTrack()
	{
		return $this->_set_last_track;
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Visitor';
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->setPrimaryTable(array(
			'name' => 'visitors',
			'indexes' => array(
				'date_last_idx' => array(
					'columns' => array('date_last'),
				),
			),
		));
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'auth', 'type' => 'string', 'length' => 15, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'auth', ));
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'name', ));
		$metadata->mapField(array( 'fieldName' => 'email', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'email', ));
		$metadata->mapField(array( 'fieldName' => 'chat_invite', 'type' => 'array', 'nullable' => true, 'columnName' => 'chat_invite', ));
		$metadata->mapField(array( 'fieldName' => 'page_count', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'page_count', ));
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_created', ));
		$metadata->mapField(array( 'fieldName' => 'date_last', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_last', ));
		$metadata->mapManyToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'initial_track', 'targetEntity' => 'Application\\DeskPRO\\Entity\\VisitorTrack', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'initial_track_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'visit_track', 'targetEntity' => 'Application\\DeskPRO\\Entity\\VisitorTrack', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'visit_track_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'last_track', 'targetEntity' => 'Application\\DeskPRO\\Entity\\VisitorTrack', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'last_track_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
	}
}
