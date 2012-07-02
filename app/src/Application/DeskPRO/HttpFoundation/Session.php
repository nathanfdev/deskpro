<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category HttpFoundation
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

		if (DP_INTERFACE != 'admin' && (!empty($_COOKIE['dpreme']) && strpos($_COOKIE['dpreme'], '-') !== false) && (empty($_SESSION['_symfony2']['auth_person_id']) || !$_SESSION['_symfony2']['auth_person_id'])) {
			list ($person_id, $cookie_code) = explode('-', $_COOKIE['dpreme'], 2);

			$person = App::getEntityRepository('DeskPRO:Person')->find($person_id);
			if ($person && $person->validateRememberMeCookieCode($cookie_code)) {
				$_SESSION['_symfony2']['auth_person_id'] = $person->getId();

				if ($person->is_agent) {
					$_SESSION['_symfony2']['active_status'] = 'available';
					$_SESSION['_symfony2']['is_chat_available'] = 1;
				}

				$this->attributes['auth_person_id'] = $person->getId();
			}
		}

		// Also make sure the user is a visitor
		$vis = null;
		if ($this->getEntity()->visitor) {
			$vis = $this->getEntity()->visitor;
		} else{
			$vis_id = empty($_COOKIE['dpvid']) ? null : $_COOKIE['dpvid'];
			if ($vis_id) {
				$vis = App::getEntityRepository('DeskPRO:Visitor')->getVisitorFromCode($vis_id);
			}
		}
		if (!$vis) {
			$vis = App::getEntityRepository('DeskPRO:Visitor')->smartFind(
				App::getRequest()->getClientIp(),
				empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT']
			);
		}

		$current_page = App::getRequest()->getUri();
		$ref_page = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];

		if (!empty($_GET['_1'])) {
			$current_page = $_GET['_1'];
		}
		if (!empty($_GET['_2'])) {
			$ref_page = $_GET['_2'];
		}

		if (!$vis) {
			$vis = new Entity\Visitor();
			$vis['ip_address'] = App::getRequest()->getClientIp() ?: '';
			$vis['user_agent'] = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
			$vis['landing_page'] = $current_page;
			$vis['ref_page'] = $ref_page;
		}

		$path = App::getRequest()->getPathInfo();
		if (!App::getRequest()->isXmlHttpRequest() && !preg_match('#^/(widget/|chat/poll|chat/send-message|download/|favicon\.ico|dp/)#', $path)) {
			$vis['last_page'] = $current_page;
		}
		$vis['person_id'] = empty($_SESSION['_symfony2']['auth_person_id']) ? null : $_SESSION['_symfony2']['auth_person_id'];
		$vis['date_last'] = new \DateTime();

		App::getOrm()->persist($vis);

		if (!$vis->id || !$this->getEntity()->visitor || $this->getEntity()->visitor->id != $vis->id) {
			$this->getEntity()->visitor = $vis;
			App::getOrm()->persist($this->getEntity());
		}

		App::getOrm()->flush();

		$this->visitor = $vis;

        if($this->getPerson() && $this->getPerson()->is_agent && !preg_match('#^/agent/(client-messages/|poller|.*/new)#', $path)) {
            $agent = $this->getPerson();
            $date_active = new \DateTime();
            list($hour, $minute) = explode(':', $date_active->format('H:i'));
            $minute = intval($minute / 5) * 5;
            $date_active->setTime($hour, $minute, 0);

            App::getDb()->executeQuery('INSERT IGNORE INTO agent_activity(agent_id, date_active) VALUES(?,?)', array($agent['id'], $date_active->format('Y-m-d H:i:s')));
        }

		$cookie = \Application\DeskPRO\HttpFoundation\Cookie::makeCookie('dpvid', $vis['visitor_code'], 'never');
		$cookie->send();

		$this->set('dpvid', $vis['id']);
		$this->set('dplast', time());
		$_SESSION['_symfony2']['dplast'] = time();
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
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getPerson()
	{
		if ($this->person !== null) return $this->person;

		$person_id = $this->get('auth_person_id');
		$person = false;

		if ($person_id) {
			$person = $this->getEntity()->person;
		}

		if (!$person) {
			$person = new \Application\DeskPRO\People\PersonGuest();
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
			$this->language = App::getDataService('Language')->get($this->get('language_id'));
		} elseif (isset($_COOKIE['dplid'])) {
			$this->language = App::getDataService('Language')->get($_COOKIE['dplid']);
		}

		if (!$this->language) {
			$this->language = App::getDataService('Language')->getDefault();
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


	/**
	 * Get a secret string
	 *
	 * @param string $secret
	 * @return string
	 */
	public function getSessionSecret($secret = '')
	{
		return $this->getEntity()->getSessionSecret($secret);
	}


	/**
	 * Check if a security token is valid
	 *
	 * @param $name
	 * @param $token
	 * @return bool
	 */
	public function checkSecurityToken($name, $token)
	{
		return $this->getEntity()->checkSecurityToken($name, $token);
	}


	/**
	 * Generate a new security token
	 *
	 * @param $name
	 * @param int $timeout
	 * @return string
	 */
	public function generateSecurityToken($name, $timeout = 43200)
	{
		return $this->getEntity()->generateSecurityToken($name, $timeout);
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
