<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category HttpFoundation
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
	 * The lcoale used for this user
	 * @var Application\DeskPRO\Entity\Locale
	 */
	protected $locale;



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

		if ($person['id']) {
			App::setCurrentPerson($person);
		}

		$this->person = $person;

		return $person;
	}



	/**
	 * Get the locale code. Note that this is the string code xx_XX. Use
	 * getLocaleObject if you want the object.
	 *
	 * (It's the code because some Symfony components expects it to be).
	 *
	 * @return string
	 */
	public function getLocale()
	{
		$locale = $this->getLocaleObject();

		return $locale['locale'];
	}



	/**
	 * Get the locale object the user wants.
	 *
	 * @return Application\DeskPRO\Entity\Locale
	 */
	public function getLocaleObject()
	{
		if ($this->locale !== null) return $this->locale;

		$person = $this->getPerson();
		if ($person['id']) {
			$this->locale = $person['locale'];
		} elseif ($this->get('locale_id')) {
			$this->locale = App::getEntityRepository('DeskPRO:Locale')->find($this->get('locale_id'));
		}

		if (!$this->locale) {
			$this->locale = App::getEntityRepository('DeskPRO:Locale')->find(App::getSetting('core.default_locale_id'));
		}

		// still no locale? we might be pre-install, lets use the fake one
		if (!$this->locale) {
			$this->locale = \Application\DeskPRO\Translate\SystemLocale::getInstance();
		}

		return $this->locale;
	}


	public function clear()
	{
		//$this->attributes = array('_flash' => $this->attributes['_flash'], '_locale' => $this->attributes['_locale']);
	}


	public function getEntityId()
    {
        return $this->storage->getEntityId();
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