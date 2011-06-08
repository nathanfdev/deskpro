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

namespace Application\DeskPRO\HttpFoundation\SessionStorage;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Session;

use Orb\Util\Strings;
use Orb\Util\Util;


/**
 * This storage uses the Session entity for storing session info.
 */
class SessionEntityStorage implements \Symfony\Component\HttpFoundation\SessionStorage\SessionStorageInterface
{
	static protected $sessionIdRegenerated = false;
    static protected $sessionStarted       = false;

	protected $options;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
    protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Application\DeskPRO\Entity\Session
	 */
	protected $session;

    public function __construct(\Doctrine\ORM\EntityManager $em, $options = null)
    {
        $this->em = $em;
		$this->db = $em->getConnection();

        $cookieDefaults = session_get_cookie_params();

        $this->options = array_merge(array(
            'name'          => 'dpsid',
            'lifetime'      => $cookieDefaults['lifetime'],
            'path'          => $cookieDefaults['path'],
            'domain'        => $cookieDefaults['domain'],
            'secure'        => $cookieDefaults['secure'],
            'httponly'      => isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false,
        ), $options);

        session_name($this->options['name']);
    }



    /**
     * Starts the session.
     */
	public function start()
	{
		if (self::$sessionStarted) {
			return;
		}

		session_set_save_handler(
			array($this, 'sessionOpen'),
			array($this, 'sessionClose'),
			array($this, 'sessionRead'),
			array($this, 'sessionWrite'),
			array($this, 'sessionDestroy'),
			array($this, 'sessionGC')
		);

		$this->options['name'] = App::getSetting('core.sessions_cookie_name');

		// this is COOKIE liftime. We always want it to be a session cookie
		// (exists until browser closes). It shouldnt be the lifetime of the session,
		// that is a seprate matter. If this is say an hour, then the users cookie is removed
		// after an hour and they loose their session even though it's still valid on the server.
		$this->options['lifetime'] = 0;

		session_set_cookie_params(
			$this->options['lifetime'],
			$this->options['path'],
			$this->options['domain'],
			$this->options['secure'],
			$this->options['httponly']
		);

		// disable native cache limiter as this is managed by HeaderBag directly
		session_cache_limiter(false);

		// We want to use our own sessionid's, so we have to do this check
		// to see if we need to create a new entity
		$session_id = empty($_COOKIE[$this->options['name']]) ? null : $_COOKIE[$this->options['name']];
		$session = null;
		if ($session_id) {
			$session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($session_id);
		}

		// Sessions are deleted on cron, but we'll also enforce it here
		$cutoff = time() - App::getSetting('core.sessions_lifetime');

		if (!$session OR $session['date_last']->getTimestamp() < $cutoff) {
			$session = new \Application\DeskPRO\Entity\Session();
			$this->em->persist($session);
			$this->em->flush();

			session_id($session->getSessionCode());

			$this->session = $session;
		}

		session_start();

		self::$sessionStarted = true;

		// We set this here to force saving of sessions
		// every time, so the date_last is updated
		$_SESSION['.'] = mt_rand(1, 999999);
	}



    /**
     * Opens a session.
     *
     * @param  string $path  (ignored)
     * @param  string $name  (ignored)
     *
     * @return boolean true, if the session was opened, otherwise an exception is thrown
     */
    public function sessionOpen($path = null, $name = null)
    {
        return true;
    }



    /**
     * Closes a session.
     *
     * @return boolean true, if the session was closed, otherwise false
     */
    public function sessionClose()
    {
        return true;
    }



    /**
     * Destroys a session.
     *
     * @param  string $id  A session ID
     *
     * @return bool   true, if the session was destroyed, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be destroyed
     */
    public function sessionDestroy($id)
    {
		$session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($id);

		$this->em->remove($session);
		$this->em->flush();

        return true;
    }



    /**
     * Cleans up old sessions. This is a noop, sessions are cleaned on cron.
     *
     * @param  int $lifetime  The lifetime of a session in seconds
     * @return bool true
     * @throws \RuntimeException If any old sessions cannot be cleaned
     */
    public function sessionGC($lifetime)
    {
        return true;
    }



    /**
     * Reads a session.
     *
     * @param  string $id  A session ID
     *
     * @return string      The session data if the session was read or created, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be read
     */
    public function sessionRead($id)
    {
		$session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($id);
		if ($session) {
			$this->session = $session;
			return $session['data'];
		}

		return '';
    }



    /**
     * Writes session data.
     *
     * @param  string $id    A session ID
     * @param  string $data  A serialized chunk of session data
     *
     * @return bool true, if the session was written, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session data cannot be written
     */
    public function sessionWrite($id, $data)
    {
		// Because of when the session is written, we cant use the ORM here,
		// because the manager has lost its reference to the session state
		$id = Session::getIdFromCode($id);

		$sess_rec = array();
		$sess_rec['data'] = $data;
		$sess_rec['date_last'] = date('Y-m-d H:i:s', time());
		$sess_rec['is_person'] = 0;
		$sess_rec['person_id'] = null;
		$sess_rec['visitor_id'] = (isset($_SESSION['_symfony2']['dpvid']) ? $_SESSION['_symfony2']['dpvid'] : null);

		if (!empty($_SESSION['_symfony2']['auth_person_id'])) {
			$sess_rec['is_person'] = 1;
			$sess_rec['person_id'] = $_SESSION['_symfony2']['auth_person_id'];
		}

		$this->db->update('sessions', $sess_rec, array('id' => $id));

        return true;
    }

	public function getId()
	{
		if (!self::$sessionStarted) {
			throw new \RuntimeException('The session must be started before reading its ID');
		}

		return session_id();
	}

	public function getEntityId()
	{
		$id = $this->getId();
		list($entity_id, ) = explode('-', $id, 2);
		$entity_id = Util::baseDecode($entity_id, 'base36');

		return $entity_id;
	}

	public function getEntity()
	{
		if (!$this->session) {
			$this->session = App::getEntityRepository('DeskPRO:Session')->find($this->getEntityId());
		}

		return $this->session;
	}

    /**
     * Reads data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key A unique key identifying your data
     *
     * @return mixed Data associated with the key
     */
    public function read($key, $default = null)
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    /**
     * Removes data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param  string $key  A unique key identifying your data
     *
     * @return mixed Data associated with the key
     */
    public function remove($key)
    {
        $retval = null;

        if (isset($_SESSION[$key])) {
            $retval = $_SESSION[$key];
            unset($_SESSION[$key]);
        }

        return $retval;
    }

    /**
     * Writes data to this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key   A unique key identifying your data
     * @param mixed  $data  Data associated with your key
     *
     */
    public function write($key, $data)
    {
        $_SESSION[$key] = $data;
    }

    /**
     * Regenerates id that represents this storage.
     *
     * @param  Boolean $destroy Destroy session when regenerating?
     *
     * @return Boolean True if session regenerated, false if error
     *
     */
    public function regenerate($destroy = false)
    {
        if (self::$sessionIdRegenerated) {
            return;
        }

        session_regenerate_id($destroy);

        self::$sessionIdRegenerated = true;
    }
}
