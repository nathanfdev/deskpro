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

/**
 * Os ticket exception (pdo exceptions)
 *
 * Class OsTicketReaderException
 * @package Application\ImportBundle\Reader\OsTicket
 */
class OsTicketReaderException extends \Exception
{
    /**
     * @var string
     */
    private $error_code;

    /**
     * @var array
     */
    private $error_info;

    /**
     * Constructor
     *
     * @param string $message
     * @param string $error_code
     * @param array  $error_info
     */
    public function __construct($message, $error_code, array $error_info = null)
    {
        parent::__construct($message);

        $this->error_code = $error_code;
        $this->error_info = $error_info;
    }

    /**
     * Returns error code
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * Returns error info
     *
     * @return array
     */
    public function getErrorInfo()
    {
        return $this->error_info;
    }
}
