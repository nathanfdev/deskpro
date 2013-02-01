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

	/**
	 * @var int
	 */
	protected $connect_timeout = 8;

	/**
	 * @var int
	 */
	protected $stream_timeout = 15;

	public function __construct($host = '', $port = null, $ssl = false, Logger $logger = null, $connect_timeout = 8, $stream_timeout = 15)
	{
		$this->logger = $logger;
		$this->connect_timeout = $connect_timeout;
		$this->stream_timeout = $stream_timeout;
		parent::__construct($host, $port, $ssl);
	}

	public function connect($host, $port = null, $ssl = false)
    {
        if ($ssl == 'SSL') {
            $host = 'ssl://' . $host;
        }

        if ($port === null) {
            $port = $ssl == 'SSL' ? 995 : 110;
        }

        $errno  =  0;
        $errstr = '';
        $this->_socket = fsockopen($host, $port, $errno, $errstr, $this->connect_timeout);
        if (!$this->_socket) {
            throw new Exception\RuntimeException('cannot connect to host; error = ' . $errstr . ' (errno = ' . $errno . ' )');
        }
		stream_set_timeout($this->_socket, $this->stream_timeout);

        $welcome = $this->readResponse();

        strtok($welcome, '<');
        $this->_timestamp = strtok('>');
        if (!strpos($this->_timestamp, '@')) {
            $this->_timestamp = null;
        } else {
            $this->_timestamp = '<' . $this->_timestamp . '>';
        }

        if ($ssl === 'TLS') {
            $this->request('STLS');
            $result = stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$result) {
                throw new Exception\RuntimeException('cannot enable TLS');
            }
        }

        return $welcome;
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