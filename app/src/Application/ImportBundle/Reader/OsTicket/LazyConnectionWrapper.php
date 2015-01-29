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

namespace Application\ImportBundle\Reader\OsTicket;

use PDO;

/**
 * Prevent exceptions if pdo configuration is not valid
 *
 * Class PdoConnection
 * @package Application\ImportBundle\Reader\OsTicket
 */
class LazyConnectionWrapper implements ConnectionWrapperInterface
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $options;

    /**
     * @var PDO
     */
    private $adapter;

    /**
     * Constructor
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param array  $options
     */
    public function __construct($dsn, $user = null, $password = null, array $options = null)
    {
        $this->dsn      = $dsn;
        $this->user     = $user;
        $this->password = $password;
        $this->options  = $options;
    }

    /**
     * Returns pdo connection
     *
     * @return PDO
     */
    public function getConnection()
    {
        if (!$this->adapter) {
            $this->adapter = new PDO(
                $this->dsn,
                $this->user,
                $this->password
            );
        }

        return $this->adapter;
    }
}
