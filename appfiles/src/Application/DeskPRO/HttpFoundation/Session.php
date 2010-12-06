<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category HttpFoundation
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\HttpFoundation;

use Orb\Util\Strings;
use Orb\Util\Util;

use \Application\DeskPRO\App;

/**
 * Session is able to load up a user, their locale etc.
 */
class Session extends \Symfony\Component\HttpFoundation\Session implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * The person this session belongs to
	 * @var Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * The language used for this user
	 * @var Application\DeskPRO\Entity\Language
	 */
	protected $language;



	/**
	 * Get the logged in Person
	 *
	 * @return Application\DeskPRO\Entity\Person
	 */
	public function getPerson()
	{
		if ($this->person !== null) return $this->person;

		$person_id = $this->get('auth_person_id');
		$person = false;

		if ($person_id) {
			try {
				$person = App::getOrm()->find('DeskPRO:Person', $person_id);
			} catch (\Doctrine\ORM\NoResultException $e) {}
		}

		if (!$person) {
			$person = new \Application\DeskPRO\Entity\PersonGuest();
		}

		$this->person = $person;

		return $person;
	}



	/**
	 * Get the locale the user wants.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		$language = $this->getLanguage();

		if (!$language) {
			return 'en_US';
		} else {
			return $language['locale'];
		}
	}


	/**
	 * Get the language the user wants.
	 *
	 * TODO: Currently there is no Language object for default (0) where phrases
	 * etc are gathered from filesystem. Perhaps this needs to change, or maybe
	 * make a LanguageDefault subclass like we did with PersonGuest.
	 *
	 * @return Application\DeskPRO\Entity\Language
	 */
	public function getLanguage()
	{
		if ($this->language !== null) return $this->language;

		$person = $this->getPerson();
		if ($person['language']) {
			$this->language = $person['language'];
		} else {
			$this->language = 0;
		}
	}


	public function clear()
	{
		//$this->attributes = array('_flash' => $this->attributes['_flash'], '_locale' => $this->attributes['_locale']);
	}



	public function getIterator()
	{
		return \ArrayIterator($this->attributes);
	}

	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}

	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetExists($offset)
	{
		return $this->has($offset);
	}
}