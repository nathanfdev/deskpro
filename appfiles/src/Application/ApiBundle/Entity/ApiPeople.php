<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

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
class ApiPeople extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id
	 * @Column(name="api_key", type="string", length=50)
	 */
	protected $api_key;

	/**
	 * @var string
	 * @orm:Id
	 * @Column(name="person_id", type="integer")
	 */
	protected $person_id;
	
	/**
	 * @var string
	 * @Column(name="secret_key", type="string", length=30)
	 */
	protected $token;

	/**
	 * @var string
	 * @Column(name="secret_key", type="string", length=30)
	 */
	protected $token_secret;

	/**
	 * A note or description about the key (ie what its used for).
	 *
	 * @var string
	 * @Column(name="note", type="text")
	 */
	protected $note = '';


	public function init()
	{
		$this->api_key = Strings::random(30, Strings::CHARS_KEY);
	}
}