<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Reader\ZenDesk;

use Exception;

/**
 * ZenDesk reader config
 *
 * Class ZenDeskConfig
 * @package Application\ImportBundle\Reader\ZenDesk
 */
class ZenDeskConfig
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
     * How many times to try an API call before re-throwing an error?
     *
     * @var int
     */
    private $try_count = 6;

    /**
     * The number of seconds between try attempts
     * when the attempts are errors;
     *
     * @var int
     */
    private $try_time_error  = 6;

    /**
     * The number of seconds between try attempts
     * when the attempts are rate limit errors.
     *
     * @var int
     */
    private $try_time_ratelimit  = 15;

    /**
     * The number of seconds between try attempts increases
     * by this number every time. So try #2 is $try_time_ratelimit,
     * try #3 is $try_time_ratelimit+$try_time_inc, etc.
     *
     * @var int
     */
    private $try_time_inc = 15;

    /**
     * Constructor
     *
     * @param string $subdomain
     * @param string $username
     */
    public function __construct($subdomain, $username)
    {
        $this->subdomain = $subdomain;
        $this->username  = $username;
    }

    /**
     * Returns the subdomain
     *
     * @return string
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     * Returns the username or email
     *
     * @return int
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the auth api token
     *
     * @param string $api_token
     * @return $this
     */
    public function setApiToken($api_token)
    {
        $this->api_token = $api_token;
        return $this;
    }

    /**
     * Returns the api token if it's defined
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->api_token;
    }

    /**
     * Set the auth password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Returns the api password if it's defined
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns a text value indicating the type of authorization configured
     *
     * @return string
     * @throws Exception
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
     * Returns auth password or token by auth type
     *
     * @return string
     * @throws Exception
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
}
