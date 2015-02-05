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

/**
 * Class ZendDeskConfig
 * @package Application\ImportBundle\Reader\ZenDesk
 */
class ZendDeskConfig
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $user_id;

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
     * @param string $host
     * @param string $user_id
     * @param string $api_token
     */
    public function __construct($host, $user_id, $api_token)
    {
        $this->host      = $host;
        $this->user_id   = $user_id;
        $this->api_token = $api_token;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->api_token;
    }
}
