<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EmailGateway\FetcherStorage;

use Application\DeskPRO\EmailGateway\Storage\Pop3;
use Orb\Log\Logger;

class Pop3Storage implements FetcherStorageInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string|null
     */
    private $user;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string|null
     */
    private $secure;

    /**
     * @var \Application\DeskPRO\EmailGateway\Storage\Pop3
     */
    private $storage = null;

    /**
     * @var
     */
    private $logger;

    /**
     * @param string      $host
     * @param string      $port
     * @param string|null $user
     * @param string|null $password
     * @param string|null $secure
     */
    public function __construct($host, $port, $user, $password, $secure = null)
    {
        $this->host = $host;
        $this->port = $port;

        if ($user !== null) {
            $this->user = $user;
        }
        if ($password !== null) {
            $this->password = $password;
        }

        if ($secure == 'ssl') {
            $this->secure = 'ssl';
        } elseif ($secure == 'tls') {
            $this->secure = 'tls';
        }
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Storage\Pop3
     */
    public function getStorage()
    {
        if ($this->storage !== null) {
            return $this->storage;
        }

        $options             = [];
        $options['host']     = $this->host;
        $options['port']     = $this->port;
        $options['user']     = $this->user ?: '';
        $options['password'] = $this->password ?: '';
        $options['ssl']      = $this->secure;

        if ($this->logger) {
            $options['logger'] = $this->logger;
        }

        $storage = new Pop3($options);

        return $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function closeStorage()
    {
        if ($this->storage !== null) {
            $this->storage->close();
            $this->storage = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
