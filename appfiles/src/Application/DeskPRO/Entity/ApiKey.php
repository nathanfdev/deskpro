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
 * An API key is a simple way to use the api without going through OAuth.
 * Admins can define an API key, and use the key to authorize requests. Useful
 * for things like system services. User services (things users want to do)
 * will want to use OAuth.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ApiKey")
 * @orm:Table(name="api_keys")
 */
class ApiKey extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="code", type="string", length=25)
	 */
	protected $code;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * A note or description about the key (ie what its used for).
	 *
	 * @var string
	 * @orm:Column(name="note", type="text")
	 */
	protected $note = '';


	public function __construct()
	{
		$this->code = Strings::random(25, Strings::CHARS_KEY);
	}
	


	/**
	 * Get a "key string". This is a combined ID and code like id:code
	 * that is used in auth lookups.
	 *
	 * @return string
	 */
	public function getKeyString()
	{
		return $this->id . ':' . $this->code;
	}
}