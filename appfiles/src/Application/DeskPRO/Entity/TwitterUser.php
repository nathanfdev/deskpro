<?php

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * Twitter User
 *
 * @orm:Table(name="twitter_users")
 * @orm:HasLifecycleCallbacks
 */
class TwitterUser extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @orm:Id
	 * @orm:GeneratedValue(strategy="NONE")
	 * @orm:Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var string
	 * @orm:Column(name="name", type="string", size="40")
	 */
	protected $name;

	/**
	 * @var string
	 * @orm:Column(name="screen_name", type="string", size="20")
	 */
	protected $screen_name;

	/**
	 * @var string
	 * @orm:Column(name="language", type="string", size="200")
	 */
	protected $profile_image_url;

	/**
	 * @var string
	 * @orm:Column(name="language", type="string", size="3")
	 */
	protected $language;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_protected", type="boolean")
	 */
	protected $is_protected = false;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_verified", type="boolean")
	 */
	protected $is_verified = false;

	/**
	 * @var string
	 * @orm:Column(name="location", type="string", size="255")
	 */
	protected $location;

	/**
	 * @var Boolean
	 * @orm:Column(name="is_geo_enabled", type="boolean")
	 */
	protected $is_geo_enabled = false;

	/**
	 * @var double
	 * @orm:Column(name="geo_latitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_latitude = null;

	/**
	 * @var double
	 * @orm:Column(name="geo_longitude", type="decimal", nullable=true, precision=10, scale=5)
	 */
	protected $geo_longitude = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * @return Boolean
	 */
	public function isProtected()
	{
		return (Boolean) $this->is_protected;
	}

	/**
	 * @return Boolean
	 */
	public function isVerified()
	{
		return (Boolean) $this->is_verified;
	}

	/**
	 * @return Boolean
	 */
	public function isGeoEnabled()
	{
		return (Boolean) $this->is_geo_enabled;
	}
}