<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Locale. A locale defines various formats (number formatting, times, dates etc),
 * as well as which language is used.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Locale")
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="locales")
 */
class Locale extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var Language
	 * @orm:ManyToOne(targetEntity="Language")
	 * @orm:JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language = null;

	/**
	 * The locale code
	 *
	 * @var string
	 * @orm:Column(name="locale", type="string", length=20)
	 */
	protected $locale = 'en_US';

	/**
	 * Title of the locale
	 *
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;


	public function getLanguageId()
	{
		if (!$this->language) {
			return 0;
		}

		return $this->language['id'];
	}

	public function setLanguageId($language_id)
	{
		$this->language = App::getEntityRepository('DeskPRO:Language')->find($language_id);
	}
}