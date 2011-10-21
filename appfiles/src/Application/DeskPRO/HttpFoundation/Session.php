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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Session is able to load up a user, their language etc.
 */
class Session extends \Symfony\Component\HttpFoundation\Session implements \ArrayAccess, \IteratorAggregate
{
	public static $track_from_input = false;

	/**
	 * The person this session belongs to
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * The lang used for this user
	 * @var \Application\DeskPRO\Entity\Language
	 */
	protected $language;

	/**
	 * The current visitor
	 * @var \Application\DeskPRO\Entity\Visitor
	 */
	protected $visitor;

	/**
	 * Starts the session storage.
	 */
	public function start()
	{
		if (true === $this->started) {
			return;
		}

		parent::start();

		// Also make sure the user is a visitor
		$vis_id = empty($_COOKIE['dpvid']) ? null : $_COOKIE['dpvid'];
		$vis = null;
		if ($vis_id) {
			$vis = App::getEntityRepository('DeskPRO:Visitor')->getVisitorFromCode($vis_id);
		}
		if (!$vis) {
			$vis = App::getEntityRepository('DeskPRO:Visitor')->smartFind(
				App::getRequest()->getClientIp(),
				empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT']
			);
		}

		$current_page = App::getRequest()->getUri();
		$ref_page = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];

		if (self::$track_from_input) {
			if (!empty($_GET['_1'])) {
				$current_page = $_GET['_1'];
			}
			if (!empty($_GET['_2'])) {
				$ref_page = $_GET['_2'];
			}
		}

		if (!$vis) {
			$vis = new Entity\Visitor();
			$vis['ip_address'] = App::getRequest()->getClientIp();
			$vis['user_agent'] = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
			$vis['landing_page'] = $current_page;
			$vis['ref_page'] = $ref_page;
		}

		if (!App::getRequest()->isXmlHttpRequest()) {
			$vis['last_page'] = $current_page;
		}
		$vis['person_id'] = empty($_SESSION['_symfony2']['auth_person_id']) ? null : $_SESSION['_symfony2']['auth_person_id'];
		$vis['date_last'] = new \DateTime();

		App::getOrm()->persist($vis);
		App::getOrm()->flush();

		$this->visitor = $vis;

		setcookie('dpvid', $vis['visitor_code'], time()+15778463);

		$this->set('dpvid', $vis['id']);
		$this->set('dplast', time());
	}



	/**
	 * Get the current visitor record
	 *
	 * @return \Application\DeskPRO\Entity\Visitor
	 */
	public function getVisitor()
	{
		return $this->visitor;
	}



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

		App::setCurrentPerson($person);

		$this->person = $person;

		return $person;
	}



	/**
	 * Get the locale code. Note that this is the string code xx_XX.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->getLanguage()->getLocale();
	}


	/**
	 * Get the language object
	 *
	 * @return \Application\DeskPRO\Entity\Language
	 */
	public function getLanguage()
	{
		if ($this->language !== null) return $this->language;

		$person = $this->getPerson();
		if ($person && !$person->isGuest()) {
			$this->language = $person->getLanguage();
		} elseif ($this->get('language_id')) {
			$this->language = App::getEntityRepository('DeskPRO:Language')->find($this->get('language_id'));
		}

		if (!$this->language) {
			$this->language = App::getEntityRepository('DeskPRO:Language')->getDefault();
		}

		// still no locale? we might be pre-install, lets use the fake one
		if (!$this->language) {
			$this->language = \Application\DeskPRO\Translate\SystemLanguage::getInstance();
		}

		return $this->language;
	}


	public function clear()
	{
		//$this->attributes = array('_flash' => $this->attributes['_flash'], '_locale' => $this->attributes['_locale']);
	}


	public function getEntityId()
    {
		if ($this->storage instanceof \Application\DeskPRO\HttpFoundation\SessionStorage\SessionEntityStorage) {
        	return $this->storage->getEntityId();
		} else {
			return 0;
		}
    }

	public function getSessionSecret($secret = '')
	{
		return $this->getEntity()->getSessionSecret($secret);
	}


	/**
	 * @return \Application\DeskPRO\Entity\Session
	 */
	public function getEntity()
	{
		return $this->storage->getEntity();
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
