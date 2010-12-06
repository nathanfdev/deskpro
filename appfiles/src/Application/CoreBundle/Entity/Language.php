<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
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
 * Settings used by the system.
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="languages")
 */
class Language extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * @var Style
	 * @orm:OneToOne(targetEntity="Language")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	
	/**
	 * Locale associated with this language.
	 *
	 * @var string
	 * @orm:Column(name="locale", type="string", length=20)
	 */
	protected $locale = 'en_US';


	/**
	 * Title of the style
	 *
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;


	public function setParentId($parent_id)
	{
		if ($this->id) {
			throw new \BadMethodCallException('You cannot change the parent_id once the record has been created. Hierarchy is fixed.');
		}

		$this->parent_id = $parent_id;
	}
}