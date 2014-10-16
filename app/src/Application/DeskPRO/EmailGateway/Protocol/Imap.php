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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\EmailGateway\Protocol;

use Orb\Log\Loggable;
use Orb\Log\Logger;
use Zend\Mail\Protocol\Exception;
use Zend\Stdlib\ErrorHandler;

class Imap extends \Zend\Mail\Protocol\Imap implements Loggable
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


	/**
	 * @param string $host
	 * @param null $port
	 * @param bool $ssl
	 * @param Logger $logger
	 * @param int $connect_timeout
	 * @param int $stream_timeout
	 */
	public function __construct($host = '', $port = null, $ssl = false, Logger $logger = null, $connect_timeout = 8, $stream_timeout = 15)
	{
		if (!$logger) {
			$logger = new Logger();
		}

		$this->logger = $logger;
		$this->connect_timeout = $connect_timeout;
		$this->stream_timeout = $stream_timeout;
		parent::__construct($host, $port, $ssl ? strtoupper($ssl) : $ssl);
	}


	/**
	 * {@inheritDoc}
	 */
	public function connect($host, $port = null, $ssl = false)
    {
		$ssl = $ssl ? strtoupper($ssl) : $ssl;

        if ($ssl == 'SSL') {
            $host = 'ssl://' . $host;
        }

        if ($port === null) {
            $port = $ssl === 'SSL' ? 993 : 143;
        }

        ErrorHandler::start();
        $this->socket = fsockopen($host, $port, $errno, $errstr, $this->connect_timeout);
        $error = ErrorHandler::stop();
        if (!$this->socket) {
            throw new Exception\RuntimeException(sprintf(
                'cannot connect to host%s',
                ($error ? sprintf('; error = %s (errno = %d )', $error->getMessage(), $error->getCode()) : '')
            ), 0, $error);
        }
		stream_set_timeout($this->socket, $this->stream_timeout);

        if (!$this->_assumedNextLine('* OK')) {
            throw new Exception\RuntimeException('host doesn\'t allow connection');
        }

        if ($ssl === 'TLS') {
            $result = $this->requestAndResponse('STARTTLS');
            $result = $result && stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$result) {
                throw new Exception\RuntimeException('cannot enable TLS');
            }
        }
    }

	/**
	 * {@inheritDoc}
	 */
	protected function _nextLine()
	{
		$line = fgets($this->socket);
		if ($line === false) {
			$this->logger->logDebug("<== !!! cannot read - connection closed?");
			throw new Exception\RuntimeException('cannot read - connection closed?');
		}

		$this->logger->logDebug("<== " . $line);

		return $line;
	}


	/**
	 * {@inheritDoc}
	 */
	public function sendRequest($command, $tokens = array(), &$tag = null)
	{
		if (!$tag) {
			++$this->tagCount;
			$tag = 'TAG' . $this->tagCount;
		}

		$line = $tag . ' ' . $command;

		foreach ($tokens as $token) {
			if (is_array($token)) {
				$this->logger->logDebug("==> " . $line . ' ' . $token[0]);
				if (fwrite($this->socket, $line . ' ' . $token[0] . "\r\n") === false) {
					$this->logger->logDebug("==> !!! cannot write - connection closed?");
					throw new Exception\RuntimeException('cannot write - connection closed?');
				}
				if (!$this->_assumedNextLine('+ ')) {
					$this->logger->logDebug("<== !!! cannot send literal string");
					throw new Exception\RuntimeException('cannot send literal string');
				}
				$line = $token[1];
			} else {
				$line .= ' ' . $token;
			}
		}

		$this->logger->logDebug("==> " . $line);
		if (fwrite($this->socket, $line . "\r\n") === false) {
			$this->logger->logDebug("==> !!! cannot write - connection closed?");
			throw new Exception\RuntimeException('cannot write - connection closed?');
		}
	}


	/**
	 * @param Logger $logger
	 */
	public function setLogger(Logger $logger = null)
	{
		$this->logger = $logger;
	}


	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}


	/**
	 * Fetchs a message by UID
	 */
	public function fetchByUid($items, $from, $to = null)
    {
        if (is_array($from)) {
            $set = implode(',', $from);
        } elseif ($to === null) {
            $set = (int) $from;
        } elseif ($to === INF) {
            $set = (int) $from . ':*';
        } else {
            $set = (int) $from . ':' . (int) $to;
        }

		$items = (array) $items;
		$use_items = $items;
		$orig_count = count($items);

		if (!in_array('UID', $items)) {
			array_unshift($use_items, 'UID');
		}

		$itemList = $this->escapeList($use_items);

        $tag = null;  // define $tag variable before first use
        $this->sendRequest('UID FETCH', array($set, $itemList), $tag);

        $result = array();
        $tokens = null; // define $tokens variable before first use
		$uid_token_pos = null;
        while (!$this->readLine($tokens, $tag)) {
            // ignore other responses
            if ($tokens[1] != 'FETCH') {
                continue;
            }
            // ignore other messages
			if ($uid_token_pos == null) {
				$uid_token_pos = array_search('UID', $tokens[2]);
			}
			if ($uid_token_pos === false || $uid_token_pos === null) {
				continue;
			}
            if ($to === null && !is_array($from) && $tokens[2][$uid_token_pos+1] != $from) {
                continue;
            }
            // if we only want one item we return that one directly
            if ($orig_count == 1) {
                if ($tokens[2][0] == $items[0]) {
                    $data = $tokens[2][1];
                } else {
                    // maybe the server send an other field we didn't wanted
                    $count = count($tokens[2]);
                    // we start with 2, because 0 was already checked
                    for ($i = 2; $i < $count; $i += 2) {
                        if ($tokens[2][$i] != $items[0]) {
                            continue;
                        }
                        $data = $tokens[2][$i + 1];
                        break;
                    }
                }
            } else {
                $data = array();
                while (key($tokens[2]) !== null) {
                    $data[current($tokens[2])] = next($tokens[2]);
                    next($tokens[2]);
                }
            }
            // if we want only one message we can ignore everything else and just return
            if ($to === null && !is_array($from) && $tokens[2][$uid_token_pos+1] == $from) {
                // we still need to read all lines
                while (!$this->readLine($tokens, $tag));
                return $data;
            }
            $result[$tokens[0]] = $data;
        }

        if ($to === null && !is_array($from)) {
            throw new Exception\RuntimeException('the single id was not found in response');
        }

        return $result;
    }


	/**
	 * STORE by UID
	 *
	 * @param array $flags
	 * @param $from
	 * @param null $to
	 * @param null $mode
	 * @param bool $silent
	 * @return array|bool
	 */
	public function storeById(array $flags, $from, $to = null, $mode = null, $silent = true)
    {
        $item = 'FLAGS';
        if ($mode == '+' || $mode == '-') {
            $item = $mode . $item;
        }
        if ($silent) {
            $item .= '.SILENT';
        }

        $flags = $this->escapeList($flags);
        if (is_array($from)) {
            $set = implode(',', $from);
        } elseif ($to === null) {
            $set = (int) $from;
        } elseif ($to === INF) {
            $set = (int) $from . ':*';
        } else {
            $set = (int) $from . ':' . (int) $to;
        }

        $result = $this->requestAndResponse('UID STORE', array($set, $item, $flags), $silent);

        if ($silent) {
            return $result ? true : false;
        }

        $tokens = $result;
        $result = array();
        foreach ($tokens as $token) {
            if ($token[1] != 'FETCH' || $token[2][0] != 'FLAGS') {
                continue;
            }
            $result[$token[0]] = $token[2][1];
        }

        return $result;
    }
}