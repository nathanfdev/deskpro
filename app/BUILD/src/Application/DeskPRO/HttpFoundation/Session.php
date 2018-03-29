<?php

/**
 * DeskPRO.
 *
 * @category HttpFoundation
 */

namespace Application\DeskPRO\HttpFoundation;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Session is able to load up a user, their language etc.
 */
class Session extends \Symfony\Component\HttpFoundation\Session\Session implements \ArrayAccess, \IteratorAggregate
{
    /**
     * The person this session belongs to.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * The lang used for this user.
     *
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $language;

    /**
     * True if this is the first page view of a session.
     *
     * @var bool
     */
    protected $is_first_page = false;

    /**
     * @var array
     */
    protected $autostart_interfaces = [
        'admin', 'agent', 'reports', 'user', 'dp',
    ];

    protected $has_run_start = false;

    public function __construct(
        \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface $storage = null,
        \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $attributes = null,
        \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashes = null
    ) {
        parent::__construct($storage, $attributes, $flashes);
    }

    /**
     * Starts the session storage.
     */
    public function start()
    {
        if (!$this->storage->isStarted()) {
            $this->storage->start();
        }

        if ($this->has_run_start) {
            return;
        }

        $this->has_run_start = true;

        $this->is_first_page = empty($_SESSION);

        $allow_rememberme = false;
        if ((DP_INTERFACE == 'reports' || DP_INTERFACE == 'billing' || DP_INTERFACE == 'admin' || DP_INTERFACE == 'agent')) {
            $allow_rememberme = (bool) App::getSetting('core.enable_agent_rememberme');
        } elseif (DP_INTERFACE == 'user') {
            $allow_rememberme = (bool) App::getSetting('core.enable_user_rememberme');
        }
        if ((empty($_SESSION['_sf2_attributes']['auth_person_id']) || (!isset($_SESSION['_sf2_attributes']['auth_person_id']) || !$_SESSION['_sf2_attributes']['auth_person_id']))) {
            // See if we should carry an agent session
            if (!empty($_COOKIE['dpsid-agent']) && (DP_INTERFACE == 'user' || DP_INTERFACE == 'reports' || DP_INTERFACE == 'billing' || DP_INTERFACE == 'admin')) {
                $sid = Entity\Session::getIdFromCode($_COOKIE['dpsid-agent']);
                if ($sid) {
                    if (App::getSetting('core.session_keepalive_require_page')) {
                        $agent_session = App::getDb()->fetchAssoc('
                            SELECT person_id, auth
                            FROM sessions
                            WHERE id = ? AND date_last > ? AND date_last_page > ?
                        ', [$sid, date('Y-m-d H:i:s', time() - App::getSetting('core.sessions_lifetime')), date(time() - App::getSetting('core.sessions_lifetime'))]);
                    } else {
                        $agent_session = App::getDb()->fetchAssoc('
                            SELECT person_id, auth
                            FROM sessions
                            WHERE id = ? AND date_last > ?
                        ', [$sid, date('Y-m-d H:i:s', time() - App::getSetting('core.sessions_lifetime'))]);
                    }

                    list(, $auth) = explode('-', $_COOKIE['dpsid-agent']);

                    if ($agent_session && $agent_session['auth'] == $auth && $agent_session['person_id']) {
                        $person = App::getEntityRepository('DeskPRO:Person')->find($agent_session['person_id']);
                        if ($person && $person->is_agent) {
                            $this->_setCurrentPerson($person);
                        }
                    }
                }
            } elseif (!empty($_COOKIE['dpreme']) && is_string($_COOKIE['dpreme']) && strpos($_COOKIE['dpreme'], '-') !== false && $allow_rememberme) {
                list($person_id, $cookie_code) = explode('-', $_COOKIE['dpreme'], 2);

                /** @var Entity\Person $person */
                $person = App::getEntityRepository('DeskPRO:Person')->find($person_id);
                if ($person && !$person->is_deleted && !$person->is_disabled && $person->validateRememberMeCookieCode($cookie_code)) {
                    $this->_setCurrentPerson($person);

                    if (defined('DP_INTERFACE') && DP_INTERFACE == 'agent') {
                        $this->set('active_status', 'available');
                        $this->set('is_chat_available', $person->getPref('agent.chat.is_available', 1));
                    }

                    // Set last login date
                    App::getDb()->update('people', ['date_last_login' => date('Y-m-d H:i:s')], ['id' => $person->getId()]);

                    // Insert log
                    if ($person->is_agent) {
                        App::getDb()->insert('login_log', [
                            'person_id'    => $person_id,
                            'area'         => DP_INTERFACE,
                            'is_success'   => 1,
                            'ip_address'   => '',
                            'hostname'     => '',
                            'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                            'date_created' => date('Y-m-d H:i:s'),
                            'via_cookie'   => 1,
                        ]);
                    }
                }
                // can we carry over an agent session in the user interface?
            } elseif (!empty($_COOKIE['dpsid-portal']) && (DP_INTERFACE == 'agent' || DP_INTERFACE == 'reports' || DP_INTERFACE == 'billing' || DP_INTERFACE == 'admin')) {
                $sid = $_COOKIE['dpsid-portal'];
                if ($sid) {
                    $agent_session = App::getDb()->fetchAssoc(
                        '
                            SELECT sess_data
                            FROM sess_data
                            WHERE sess_id = ?
                        ',
                        [
                            $sid,
                        ]
                    );
                    $agentSessData = base64_decode($agent_session['sess_data']);
                    $personId      = preg_replace('/^.+s:14:"auth_person_id";i:(\d+);.+$/', '\\1', $agentSessData);

                    if ($personId) {
                        $person = App::getEntityRepository('DeskPRO:Person')->find($personId);
                        if ($person && $person->is_agent) {
                            $this->_setCurrentPerson($person);
                        }
                    }
                }
            }
        }

        if (DP_INTERFACE == 'user' && $this->is_first_page && empty($_COOKIE['dplogout'])) {
            // user interface and a new session - we need to look through user sources for cookie handlers
            $sources = App::getEntityRepository('DeskPRO:Usersource')->getCookieInputUsersources();
            foreach ($sources as $source) {
                /* @var $source \Application\DeskPRO\Entity\Usersource */
                $adapter = $source->getAdapter()->getAuthAdapter();

                if ($adapter instanceof \Orb\Auth\Adapter\CookieLoginInterface) {
                    $userinfo = $adapter->authenticateCookie($_COOKIE);
                    if (!$userinfo) {
                        continue;
                    }

                    $identity = $adapter->getIdentityFromUserInfo($userinfo);

                    $login_processor = new \Application\DeskPRO\Auth\LoginProcessor($source, $identity);
                    $person          = $login_processor->getPerson();

                    $this->_setCurrentPerson($person);
                    break;
                }
            }
        }

        // See if we need to carry an admin session
        if (DP_INTERFACE == 'user' && !$this->person && isset($_GET['admin_portal_controls'])) {
            $admin_session_code = !empty($_COOKIE['dpsid-admin']) ? $_COOKIE['dpsid-admin'] : false;
            $admin_session      = null;
            if ($admin_session_code) {
                $admin_session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($admin_session_code);
                if (!$admin_session || !$admin_session->person || !$admin_session->person->is_agent) {
                    $admin_session = null;
                }

                if ($admin_session && $admin_session->person) {
                    $this->_setCurrentPerson($admin_session->person);
                }
            }
        }

        $user_ip = '';

        $path = '';
        if (App::getContainer()->isScopeActive('request')) {
            $path = App::getRequest()->getPathInfo();
        }

        $url = '';
        if (App::getContainer()->isScopeActive('request')) {
            $url = App::getRequest()->getUri();
        }

        // Set this for SessionEntityStorage
        // which is usually dumb of the app, but we want to use
        // getClientIp method because we might be using a proxy-passed
        // IP, but dont want to tie the App/container/request into SessionEntityStorage
        $GLOBALS['DP_CURRENT_USER_IP'] = $user_ip;

        if ($this->getPerson() && $this->getPerson()->is_agent && !preg_match('#^/agent/(client-messages/|poller|.*/new)#', $path) && !preg_match('#\.json(\?.*?)?$#', $path) && empty($_GET['dp_no_activity'])) {
            $agent               = $this->getPerson();
            $date_active         = new \DateTime();
            list($hour, $minute) = explode(':', $date_active->format('H:i'));
            $minute              = intval($minute / 5) * 5;
            $date_active->setTime($hour, $minute, 0);

            App::getDb()->executeQuery('INSERT IGNORE INTO agent_activity(agent_id, date_active) VALUES(?,?)', [$agent['id'], $date_active->format('Y-m-d H:i:s')]);
        }

        $this->set('dplast', time());
        $this->set('dplastpage', time());

        if (defined('DP_INTERFACE')) {
            $this->set('dp_interface', DP_INTERFACE);
        }

        $person = $this->getPerson();
        if ($person) {
            App::setCurrentPerson($person);
        } else {
            App::setCurrentPerson(null);
        }

        $me = $this;
        \DpShutdown::add(function () use ($me) {
            $me->save();
        });
    }

    protected function _setCurrentPerson(\Application\DeskPRO\Entity\Person $person)
    {
        if (DP_INTERFACE == 'user' && $person->is_disabled) {
            // can't login as this person
            return;
        }

        $this->person = $person;
        App::setCurrentPerson($person);
        if ($person->is_agent) {
            $this->attributes['active_status']     = 'available';
            $this->attributes['is_chat_available'] = $person->getPref('agent.chat.is_available', 1);
        }

        $this->set('auth_person_id', $person->getId());
    }

    /**
     * Get the logged in Person.
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPerson()
    {
        if ($this->person !== null) {
            return $this->person;
        }

        if (defined('DP_INTERFACE') && in_array(DP_INTERFACE, $this->autostart_interfaces)) {
            $this->start();
        }

        $person = false;
        if ($this->isStarted()) {
            $person_id = $this->get('auth_person_id');

            if (App::getCurrentPerson() && App::getCurrentPerson()->getId() == $person_id) {
                $person = App::getCurrentPerson();
            } else {
                if ($person_id) {
                    $person = $this->getEntity()->person;
                }
            }

            if (DP_INTERFACE == 'user' && $person && $person->is_disabled) {
                $person = false;
            }
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
     * Get the language object.
     *
     * @return \Application\DeskPRO\Entity\Language
     */
    public function getLanguage()
    {
        if ($this->language !== null) {
            return $this->language;
        }

        $person = $this->getPerson();
        if ($person && !$person->isGuest()) {
            $this->language = $person->getLanguage();
        } elseif ($this->isStarted() && $this->get('language_id')) {
            $this->language = App::getDataService('Language')->get($this->get('language_id'));
        } elseif (isset($_COOKIE['dplid'])) {
            $this->language = App::getDataService('Language')->get($_COOKIE['dplid']);
        }

        if (!$this->language) {
            $data       = App::getDataService('Language');
            $languages  = $data->getAll();
            $default_id = $data->getDefaultId();

            $locales = [''];
            foreach ($languages as $language) {
                $locales[] = $language->locale;
            }

            try {
                // get the highest priority language if available
                $locale           = App::getRequest()->getPreferredLanguage($locales);
                $accept_languages = App::getRequest()->getLanguages();
            } catch (\Symfony\Component\DependencyInjection\Exception\InactiveScopeException $e) {
                // the request may not be available, so use the default lang
                $locale           = '';
                $accept_languages = [];
            }

            if ($locale) {
                // we have an exact locale match
                foreach ($languages as $language) {
                    if ($language->locale === $locale) {
                        $this->language = $language;
                        break;
                    }
                }
            } else {
                // look for a language match (as there isn't an exact locale match)
                foreach ($accept_languages as $accept_language) {
                    $accept_language = substr($accept_language, 0, 2);
                    foreach ($languages as $language) {
                        if (substr($language->locale, 0, 2) == $accept_language) {
                            $this->language = $language;
                            break 2;
                        }
                    }
                }
            }

            if (!$this->language && isset($languages[$default_id])) {
                $this->language = $languages[$default_id];
            }
        }

        // still no locale? we might be pre-install, lets use the fake one
        if (!$this->language) {
            $this->language = \Application\DeskPRO\Translate\SystemLanguage::getInstance();
        }

        // Make sure the language is complete for the interface we're seeing
        if (DP_INTERFACE == 'agent' && !$this->language->has_agent) {
            $this->language = \Application\DeskPRO\Translate\SystemLanguage::getInstance();
        } elseif ($this->language->has_admin && (DP_INTERFACE == 'admin' || DP_INTERFACE == 'reports' || DP_INTERFACE == 'billing')) {
            $this->language = \Application\DeskPRO\Translate\SystemLanguage::getInstance();
        }

        return $this->language;
    }

    /**
     * Is this the first page of the session?
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->is_first_page;
    }

    /**
     * Clears all data in the session.
     */
    public function clear()
    {
        $this->storage->clear();
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getEntity()->getId();
    }

    /**
     * Get a secret string.
     *
     * @param string $secret
     *
     * @return string
     */
    public function getSessionSecret($secret = '')
    {
        return $this->getEntity()->getSessionSecret($secret);
    }

    /**
     * Check if a security token is valid.
     *
     * @param $name
     * @param $token
     *
     * @return bool
     */
    public function checkSecurityToken($name, $token)
    {
        return $this->getEntity()->checkSecurityToken($name, $token);
    }

    /**
     * Generate a new security token.
     *
     * @param     $name
     * @param int $timeout
     *
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
        if (!$this->isStarted()) {
            $this->start();
        }

        return $this->storage->getEntity();
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setFlash($name, $value)
    {
        $this->getFlashBag()->set($name, !is_array($value) ? ['value' => $value] : $value);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return array
     */
    public function getFlash($name, array $default = [])
    {
        return $this->getFlashBag()->get($name, $default);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFlash($name)
    {
        return $this->getFlashBag()->has($name);
    }

    /**
     * @return bool
     */
    public function hasAnyFlashes()
    {
        return count($this->getFlashBag()->peekAll()) > 0;
    }

    /**
     * @return array
     */
    public function getAllFlahses()
    {
        return $this->getFlashBag()->all();
    }

    /**
     * @param string $k
     * @param mixed  $v
     */
    public function set($k, $v)
    {
        if ($k == 'language_id') {
            $this->language = null;
            $this->getLanguage();
        }

        return parent::set($k, $v);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->attributes);
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

    public function __destruct()
    {
        // We save on our own shutdown caller set up in the constructor,
        // rather than destruct where other objects might've been cleaned up already
    }
}
