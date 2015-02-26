<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\HttpFoundation\SessionStorage;

use Application\DeskPRO\App;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

/**
 * This storage uses the Session entity for storing session info.
 */
class SessionEntityStorage implements \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface
{
    /** @var bool */
    public $noSave = false;
    /** @var bool */
    protected $started = false;
    /** @var bool */
    protected $closed = false;

    /**
     * Array of SessionBagInterface
     *
     * @var SessionBagInterface[]
     */
    protected $bags;

    /**
     * @var MetadataBag
     */
    protected $metadataBag;

    /**
     * @var array
     */
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

    protected $last_save_hash = null;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param null                        $options
     */
    public function __construct(\Doctrine\ORM\EntityManager $em, $options = null)
    {
        $this->em = $em;
        $this->db = $em->getConnection();

        $cookieDefaults = session_get_cookie_params();

        $cookie_name = 'dpsid';
        if (DP_INTERFACE == 'agent' || DP_INTERFACE == 'reports' || DP_INTERFACE == 'billing' || DP_INTERFACE == 'admin') {
            $cookie_name .= '-' . DP_INTERFACE;
        }

        $this->options['name'] = $cookie_name;

        $cookieDefaults['domain']   = App::getSetting('core.cookie_domain');
        $cookieDefaults['path']     = App::getSetting('core.cookie_path');
        $cookieDefaults['httponly'] = true;

        if (\Orb\Util\Web::getRequestProtocol() == 'HTTPS') {
            $cookieDefaults['secure'] = true;
        }

        $this->options = array_merge(array(
            'name'          => $cookie_name,
            'lifetime'      => $cookieDefaults['lifetime'],
            'path'          => $cookieDefaults['path'],
            'domain'        => $cookieDefaults['domain'],
            'secure'        => $cookieDefaults['secure'],
            'httponly'      => isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false,
        ), $options);

        session_name($this->options['name']);

        $this->setMetadataBag();
    }


    /**
     * Starts the session.
     */
    public function start()
    {
        if ($this->started && !$this->closed) {
            return true;
        }

        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );

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
        if (isset($_REQUEST['__sid'])) {
            $session_id = $_REQUEST['__sid'];
        }
        $session = null;
        if ($session_id) {
            $session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($session_id);
        }

        // Sessions are deleted on cron, but we'll also enforce it here
        $cutoff = time() - App::getSetting('core.sessions_lifetime');

        $is_valid = ($session AND $session['date_last']->getTimestamp() > $cutoff);
        if ($is_valid && App::getSetting('core.session_keepalive_require_page') && $session['date_last_page']) {
            if ($session['date_last_page']->getTimestamp() < $cutoff) {
                $is_valid = false;
            }
        }

        if (!$is_valid) {
            $session = new \Application\DeskPRO\Entity\Session();

            if (\Orb\Util\Web::isBotUseragent()) {
                $session->is_bot = true;
            }

            $this->em->persist($session);
            $this->em->flush();
        }

        $is_started = (bool)session_id();

        session_id($session->getSessionCode());
        $this->session = $session;

        if (!$is_started) {
            session_start();
        }

        $this->loadSession();

        $this->started = true;
        $this->closed = false;
    }

    /**
     * Opens a session.
     *
     * @param string $path (ignored)
     * @param string $name (ignored)
     *
     * @return boolean true, if the session was opened, otherwise an exception is thrown
     */
    public function open($path = null, $name = null)
    {
        return true;
    }

    /**
     * Closes a session.
     *
     * @return boolean true, if the session was closed, otherwise false
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroys a session.
     *
     * @param string $id A session ID
     *
     * @return bool true, if the session was destroyed, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be destroyed
     */
    public function destroy($id)
    {
        if ($this->session && $this->session->getSessionCode() == $id) {
            $session = $this->session;
        } else {
            $session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($id);
        }

        if ($session && $session->getId()) {
            try {
                $this->db->delete('sessions', array('id' => $session->getId()));
                $this->em->detach($session);
            } catch (\Exception $e) {}
        }

        return true;
    }

    /**
     * Cleans up old sessions. This is a noop, sessions are cleaned on cron.
     *
     * @param  int               $lifetime The lifetime of a session in seconds
     * @return bool              true
     * @throws \RuntimeException If any old sessions cannot be cleaned
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Reads a session.
     *
     * @param string $id A session ID
     *
     * @return string The session data if the session was read or created, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be read
     */
    public function read($id)
    {
        if ($this->session && $this->session->getSessionCode() == $id) {
            $session = $this->session;
        } else {
            $session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($id);
        }

        if ($session) {
            $this->session = $session;

            return $session['data'];
        }

        return '';
    }

    /**
     * Writes session data.
     *
     * @param string $id   A session ID
     * @param string $data A serialized chunk of session data
     *
     * @return bool true, if the session was written, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session data cannot be written
     */
    public function write($id, $data)
    {
        if ($this->noSave) {
            return;
        }
        // Because of when the session is written, we cant use the ORM here,
        // because the manager has lost its reference to the session state
        $id = self::getIdFromCode($id);

        $save_hash = md5($id . $data);

        // No changes were made to the session
        if ($this->last_save_hash && $this->last_save_hash == $save_hash) {
            return true;
        }

        $this->last_save_hash = $save_hash;

        $sess_rec = array();
        $sess_rec['data'] = $data;
        $sess_rec['date_last'] = isset($_SESSION['_sf2_attributes']['dplast']) ? date('Y-m-d H:i:s', $_SESSION['_sf2_attributes']['dplast']) : date('Y-m-d H:i:s', time());

        if (isset($_SESSION['_sf2_attributes']['dplastpage'])) {
            $sess_rec['date_last_page'] = date('Y-m-d H:i:s', $_SESSION['_sf2_attributes']['dplastpage']);
        }

        $sess_rec['is_person'] = 0;
        $sess_rec['person_id'] = null;
        $sess_rec['visitor_id'] = (isset($_SESSION['_sf2_attributes']['dpvid']) ? $_SESSION['_sf2_attributes']['dpvid'] : null);

        if (!empty($GLOBALS['DP_CURRENT_USER_IP'])) {
            $sess_rec['ip_address'] = $GLOBALS['DP_CURRENT_USER_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $sess_rec['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }

        if (empty($sess_rec['ip_address'])) {
            $sess_rec['ip_address'] = '';
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $sess_rec['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (!empty($_SESSION['_sf2_attributes']['auth_person_id'])) {
            $sess_rec['is_person'] = 1;
            $sess_rec['person_id'] = $_SESSION['_sf2_attributes']['auth_person_id'];

            if (!empty($_SESSION['_sf2_attributes']['dp_interface']) && $_SESSION['_sf2_attributes']['dp_interface'] == 'agent') {
                if (!empty($_SESSION['_sf2_attributes']['active_status'])) {
                    $sess_rec['active_status'] = $_SESSION['_sf2_attributes']['active_status'];
                } else {
                    $sess_rec['active_status'] = '';
                }
                if ($sess_rec['active_status'] == 'available') {
                    $sess_rec['is_chat_available'] = isset($_SESSION['_sf2_attributes']['is_chat_available']) ? (int)$_SESSION['_sf2_attributes']['is_chat_available'] : 0;
                } else {
                    $sess_rec['is_chat_available'] = 0;
                }
            }
        } else {
            $sess_rec['active_status'] = '';
            $sess_rec['is_chat_available'] = 0;
        }

        if (isset($_SESSION['_sf2_attributes']['dp_interface'])) {
            $sess_rec['interface'] = $_SESSION['_sf2_attributes']['dp_interface'];

            if ($sess_rec['interface'] != 'agent') {
                $sess_rec['active_status'] = '';
                $sess_rec['is_chat_available'] = 0;
            }
        } else {
            $sess_rec['interface'] = '';
        }

        if (isset($GLOBALS['DP_NON_HELPDESK_SESSION']) && !$this->session->is_helpdesk) {
            $sess_rec['is_helpdesk'] = 0;
        } else {
            $sess_rec['is_helpdesk'] = 1;
        }

        try {
            $this->db->update('sessions', $sess_rec, array('id' => $id));
        } catch (\Exception $e) {
            // Cron periodically clears things like visitor tracks, so there could in rare
            // cases be an update where the visitor is no longer valid by the time this session is written
            unset($sess_rec['visitor_id']);
            $this->db->update('sessions', $sess_rec, array('id' => $id));
        }

        return true;
    }

    public function getId()
    {
        if (!$this->started) {
            throw new \RuntimeException('The session must be started before reading its ID');
        }

        return session_id();
    }

    public function setId($id)
    {
        session_id($id);
    }

    public function getEntityId()
    {
        if (!$this->started) {
            throw new \RuntimeException('The session must be started before reading its ID');
        }

        if ($this->session) {
            return $this->session->getId();
        }

        $id = $this->getId();
        list($entity_id, ) = explode('-', $id, 2);
        $entity_id = Util::baseDecode($entity_id, 'base36');

        return $entity_id;
    }

    public function getEntity()
    {
        if (!$this->started) {
            throw new \RuntimeException('The session must be started before reading its ID');
        }

        if (!$this->session) {
            $this->session = App::getEntityRepository('DeskPRO:Session')->find($this->getEntityId());
        }

        return $this->session;
    }

    /**
     * @static
     * @param  string   $sess_code
     * @return int|null
     */
    public static function getIdFromCode($sess_code)
    {
        if (!strpos($sess_code, '-')) return null;

        list ($session_id, ) = explode('-', $sess_code, 2);

        $alphabet = str_split('0123456789abcdefghijklmnopqrstuvwxyz');
        $base     = sizeof($alphabet);
        $strlen   = strlen($session_id);
        $num = 0;
        $idx = 0;

        $s = str_split($session_id);
        $tebahpla = array_flip($alphabet);

        foreach ($s as $char) {
            // Invalid character found in string
            if (!isset($tebahpla[$char])) {
                return null;
            }
            $power = ($strlen - ($idx + 1));
            $num += $tebahpla[$char] * (pow($base, $power));
            $idx += 1;
        }

        return $num;
    }

     /**
     * Returns the session name
     *
     * @return mixed The session name.
     *
     * @api
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Sets the session name
     *
     * @param string $name
     *
     * @api
     */
    public function setName($name)
    {
        session_name($name);
    }

    public function regenerate($destroy = false, $lifetime = null)
    {
        if (null !== $lifetime) {
            ini_set('session.cookie_lifetime', $lifetime);
        }

        if ($destroy) {
            $this->metadataBag->stampNew();
        }

        $ret = session_regenerate_id($destroy);

        if ($this->isStarted() && $this->getEntity()) {

            session_write_close();
            $session = new \Application\DeskPRO\Entity\Session();

            // hardcoded copy of old session params
            $copyProps = array('interface', 'person', 'visitor', 'user_agent', 'ip_address', 'is_person', 'is_bot',
                'is_helpdesk', 'active_status', 'is_chat_available');
            foreach ($copyProps as $prop) {
                $session[$prop] = $this->session[$prop];
            }

            if (!$destroy) {
                $session['data'] = $this->session['data'];
            }

            $this->session = $session;
            $this->em->persist($session);
            $this->em->flush();

            session_id($session->getSessionCode());
            session_start();
        }

        $this->loadSession();

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        // Dont close the session here, we do it on shutdown automatically
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        // clear out the bags
        foreach ($this->bags as $bag) {
            $bag->clear();
        }

        // clear out the session
        $_SESSION = array();

        // reconnect the bags to the session
        $this->loadSession();
    }

    /**
     * {@inheritdoc}
     */
    public function registerBag(SessionBagInterface $bag)
    {
        $this->bags[$bag->getName()] = $bag;
    }

    /**
     * {@inheritdoc}
     */
    public function getBag($name)
    {
        if (!isset($this->bags[$name])) {
            throw new \InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
        }

        if ($this->started) {
            $this->loadSession();
        } else {
            $this->start();
        }

        return $this->bags[$name];
    }

    /**
     * Sets the MetadataBag.
     *
     * @param MetadataBag $metaBag
     */
    public function setMetadataBag(MetadataBag $metaBag = null)
    {
        if (null === $metaBag) {
            $metaBag = new MetadataBag();
        }

        $this->metadataBag = $metaBag;
    }

    /**
     * Gets the MetadataBag.
     *
     * @return MetadataBag
     */
    public function getMetadataBag()
    {
        return $this->metadataBag;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Load the session with attributes.
     *
     * After starting the session, PHP retrieves the session from whatever handlers
     * are set to (either PHP's internal, or a custom save handler set with session_set_save_handler()).
     * PHP takes the return value from the read() handler, unserializes it
     * and populates $_SESSION with the result automatically.
     *
     * @param array|null $session
     */
    protected function loadSession(array &$session = null)
    {
        if (null === $session) {
            $session = &$_SESSION;
        }

        $bags = array_merge($this->bags, array($this->metadataBag));

        foreach ($bags as $bag) {
            $key = $bag->getStorageKey();
            $session[$key] = isset($session[$key]) ? $session[$key] : array();
            $bag->initialize($session[$key]);
        }
    }

    /**
     * Sets session.* ini variables.
     *
     * For convenience we omit 'session.' from the beginning of the keys.
     * Explicitly ignores other ini keys.
     *
     * @param array $options Session ini directives array(key => value).
     *
     * @see http://php.net/session.configuration
     */
    public function setOptions(array $options)
    {
        $validOptions = array_flip(array(
            'cache_limiter', 'cookie_domain', 'cookie_httponly',
            'cookie_lifetime', 'cookie_path', 'cookie_secure',
            'entropy_file', 'entropy_length', 'gc_divisor',
            'gc_maxlifetime', 'gc_probability', 'hash_bits_per_character',
            'hash_function', 'name', 'referer_check',
            'serialize_handler', 'use_cookies',
            'use_only_cookies', 'use_trans_sid', 'upload_progress.enabled',
            'upload_progress.cleanup', 'upload_progress.prefix', 'upload_progress.name',
            'upload_progress.freq', 'upload_progress.min-freq', 'url_rewriter.tags',
        ));

        foreach ($options as $key => $value) {
            if (isset($validOptions[$key])) {
                ini_set('session.'.$key, $value);
            }
        }
    }
}
