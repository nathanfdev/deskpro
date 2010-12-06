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
 * API keys are codes that authorize requests against the DeskPRO.
 *
 * @orm:Entity
 * @orm:Table(name="api_keys")
 */
class ApiKey extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @orm:Id
	 * @Column(name="api_key", type="string", length=50)
	 */
	protected $api_key;

	/**
	 * @var string
	 * @orm:Column(name="api_secret", type="string", length=50)
	 */
	protected $api_secret;

	/**
	 * A note or description about the key (ie what its used for).
	 *
	 * @var string
	 * @orm:Column(name="note", type="text")
	 */
	protected $note = '';


	public function init()
	{
		$this->api_key    = Strings::random(50, Strings::CHARS_KEY);
		$this->api_secret = Strings::random(50, Strings::CHARS_KEY);
	}
}