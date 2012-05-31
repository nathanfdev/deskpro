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
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person = null;

	/**
	 * The users IP address
	 *
	 * @var string
	 */
	protected $ip_address;

	/**
	 * The users user agent string
	 *
	 * @var string
	 */
	protected $user_agent = '';

	/**
	 * The page the user came from
	 *
	 * @var string
	 */
	protected $ref_page = '';

	/**
	 * The page the user came from
	 *
	 * @var string
	 */
	protected $landing_page = '';

	/**
	 * The last page the user was on
	 *
	 * @var string
	 */
	protected $last_page = '';

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
	 * @var \DateTime
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 */
	protected $date_last;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	protected $_changed_last_page = false;
	protected $is_new = false;

	public function __construct()
	{
		$this->is_new = true;
		$this->setModelField('auth', Strings::random(15, Strings::CHARS_KEY));
		$this->setModelField('date_created', new \DateTime());
		$this->setModelField('date_last', new \DateTime());
	}

	public function isNew()
	{
		return $this->is_new;
	}



	/**
	 * Gets the ID for this vis. It's an encoded ID and an authcode.
	 *
	 * @return string
	 */
	public function getVisitorCode()
	{
		$id_enc = Util::baseEncode($this->id, Util::BASE36_ALPHABET);
		return $id_enc . '-' . $this->auth;
	}


	/**
	 * Set the last page
	 * @param string $page
	 * @return void
	 */
	public function setLastPage($page)
	{
		if ($page != $this->last_page) {
			$this->setModelField('last_page', $page);
			$this->_changed_last_page = true;
		}
	}

	public function hasChangedLastPage()
	{
		return $this->_changed_last_page;
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



	public function setPerson(Person $person = null)
	{
		if (!$person) {
			$this->setPersonId(0);
		} else {
			$this->setPersonId($person['id']);
		}
	}

	public function setPersonId($person_id)
	{
		if ($person_id) {
			$this->setModelField('person', App::getEntityRepository('DeskPRO:Person')->find($person_id));
		} else {
			$this->setModelField('person', null);
		}
	}

	public function getPersonId()
	{
		if ($this->person) {
			return $this->person['id'];
		}
		return 0;
	}

	public function updateLastTime()
	{
		$this->setModelField('date_last', new \DateTime());
	}

	public static function getIdFromCode($vis_code)
	{
		if (!strpos($vis_code, '-')) return null;

		list ($vis_id, ) = explode('-', $vis_code, 2);
		$vis_id = Util::baseDecode($vis_id, Util::BASE36_ALPHABET);

		return $vis_id;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Visitor';
		$metadata->setPrimaryTable(array( 'name' => 'visitors', 'indexes' => array( 'date_last_idx' => array( 'columns' => array( 0 => 'date_last', ), ), ), ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'auth', 'type' => 'string', 'length' => 15, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'auth', ));
		$metadata->mapField(array( 'fieldName' => 'ip_address', 'type' => 'string', 'length' => 80, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'ip_address', ));
		$metadata->mapField(array( 'fieldName' => 'user_agent', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'user_agent', ));
		$metadata->mapField(array( 'fieldName' => 'ref_page', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'ref_page', ));
		$metadata->mapField(array( 'fieldName' => 'landing_page', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'landing_page', ));
		$metadata->mapField(array( 'fieldName' => 'last_page', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'last_page', ));
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'name', ));
		$metadata->mapField(array( 'fieldName' => 'email', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'email', ));
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_created', ));
		$metadata->mapField(array( 'fieldName' => 'date_last', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_last', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
	}
}
