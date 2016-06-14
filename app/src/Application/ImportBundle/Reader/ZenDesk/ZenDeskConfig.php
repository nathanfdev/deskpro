<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Reader\ZenDesk;

use Application\ImportBundle\Reader\ReaderConfigInterface;
use DateTime;
use Exception;

/**
 * ZenDesk reader config.
 *
 * Class ZenDeskConfig
 */
class ZenDeskConfig implements ReaderConfigInterface
{
    const AUTH_TYPE_PASSWORD = 'password';
    const AUTH_TYPE_TOKEN    = 'token';

    /**
     * @var string
     */
    private $subdomain;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $api_token;

    /**
     * @var DateTime
     */
    private $initial_time;

    /**
     * @var int
     */
    private $connection_timeout = 120;

    /**
     * Constructor.
     *
     * @param string   $subdomain
     * @param string   $username
     * @param DateTime $initial_time
     */
    public function __construct($subdomain, $username, DateTime $initial_time)
    {
        $this->subdomain    = $subdomain;
        $this->username     = $username;
        $this->initial_time = $initial_time;
    }

    /**
     * Returns the subdomain.
     *
     * @return string
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     * Returns the username or email.
     *
     * @return int
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns initial start time.
     *
     * @return DateTime
     */
    public function getInitialTime()
    {
        return $this->initial_time;
    }

    /**
     * Set the auth api token.
     *
     * @param string $api_token
     *
     * @return $this
     */
    public function setApiToken($api_token)
    {
        $this->api_token = $api_token;

        return $this;
    }

    /**
     * Returns the api token if it's defined.
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->api_token;
    }

    /**
     * Set the auth password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returns the api password if it's defined.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns connection timeout.
     *
     * @return int
     */
    public function getConnectionTimeout()
    {
        return $this->connection_timeout;
    }

    /**
     * Set curl connection timeout.
     *
     * @param int $connection_timeout
     *
     * @return $this
     */
    public function setConnectionTimeout($connection_timeout)
    {
        $this->connection_timeout = (int) $connection_timeout;

        return $this;
    }

    /**
     * Returns a text value indicating the type of authorization configured.
     *
     * @throws Exception
     *
     * @return string
     */
    public function getAuthType()
    {
        if ($this->api_token) {
            return self::AUTH_TYPE_TOKEN;
        }
        if ($this->password) {
            return self::AUTH_TYPE_PASSWORD;
        }

        throw new Exception('Auth credentials is not set up');
    }

    /**
     * Returns auth password or token by auth type.
     *
     * @throws Exception
     *
     * @return string
     */
    public function getAuthValue()
    {
        if ($this->api_token) {
            return $this->api_token;
        }
        if ($this->password) {
            return $this->password;
        }

        throw new Exception('Auth credentials is not set up');
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data)
    {
        if (!$time = @$data['initial_time']) {
            $time = '-2 years';
        } else {
            if (is_numeric($time)) {
                $time = '@'.$time;
            }
        }

        $inst = new self(
            $data['subdomain'],
            $data['username'],
            new \DateTime($time)
        );

        $inst->setPassword(@$data['password']);
        $inst->setApiToken(@$data['token']);
        $inst->setConnectionTimeout(@$data['connection_timeout']);

        return $inst;
    }
}
