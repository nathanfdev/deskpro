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
 * @subpackage
 */

namespace Application\DeskPRO\EmailGateway\Protocol;

use Orb\Log\Logger;
use Zend\Mail\Protocol\Exception;

class Pop3 extends \Zend\Mail\Protocol\Pop3
{
	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	public function __construct($host = '', $port = null, $ssl = false, Logger $logger = null)
	{
		$this->logger = $logger;
		parent::__construct($host, $port, $ssl);
	}

	public function setLogger(Logger $logger = null)
	{
		$this->logger = $logger;
	}

	public function sendRequest($request)
	{
		if ($this->logger) {
			if (strpos($request, 'PASS ') === 0) {
				$this->logger->logDebug("[Request] PASS xxxxxx");
			} else {
				$this->logger->logDebug("[Request] " . $request);
			}
		}

		return parent::sendRequest($request);
	}

	public function readResponse($multiline = false)
	{
		$result = @fgets($this->_socket);
        if (!is_string($result)) {
			if ($this->logger) $this->logger->logDebug("[Response] read failed - connection closed?");
            throw new Exception\RuntimeException('read failed - connection closed?');
        }

        $result = trim($result);
        if (strpos($result, ' ')) {
            list($status, $message) = explode(' ', $result, 2);
        } else {
            $status = $result;
            $message = '';
        }

        if ($status != '+OK') {
			if ($this->logger) $this->logger->logDebug("[Response] $status");
            throw new Exception\RuntimeException('last request failed');
        }

        if ($multiline) {
            $message = '';
            $line = fgets($this->_socket);
			$log_msg = '';
            while ($line && rtrim($line, "\r\n") != '.') {
                if ($line[0] == '.') {
                    $line = substr($line, 1);
                }
                $message .= $line;
                $line = fgets($this->_socket);
				if ($this->logger && !isset($log_msg[1000])) {
					$log_msg .= $line;
				}
            }
			if ($this->logger) $this->logger->logDebug("[Response] $status $log_msg");
        }

        return $message;
	}
}