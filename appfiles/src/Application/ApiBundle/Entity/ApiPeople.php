<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Some API calls are done on behalf of a user. These map keys to authorized
 * users.
 *
 * @orm:Entity
 * @orm:Table(name="api_people")
 */
class ApiPeople extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id
	 * @orm:Column(name="api_key", type="string", length=50)
	 */
	protected $api_key;

	/**
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;
	
	/**
	 * @var string
	 * @orm:Column(name="token", type="string", length=30)
	 */
	protected $token;

	/**
	 * @var string
	 * @orm:Column(name="token_secret", type="string", length=30)
	 */
	protected $token_secret;

	/**
	 * A note or description about the key (ie what its used for).
	 *
	 * @var string
	 * @orm:Column(name="note", type="text")
	 */
	protected $note = '';


	public function init()
	{
		$this->api_key = Strings::random(30, Strings::CHARS_KEY);
	}
}