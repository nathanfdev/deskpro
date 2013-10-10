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

namespace Application\DeskPRO\EmailGateway\Storage;

use Application\DeskPRO\EmailGateway\Protocol\Imap as ImapProtocol;
use Orb\Util\Arrays;
use Zend\Mail\Protocol\Exception;

class Imap extends \Zend\Mail\Storage\Imap
{
	const ERR_CONNECT = 1;
	const ERR_LOGIN = 2;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Protocol\Imap
	 */
	protected $protocol;

	/**
	 * {@inheritDoc}
	 */
	public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object) $params;
        }

        $this->has['flags'] = true;

        if ($params instanceof \Zend\Mail\Protocol\Imap) {
            $this->protocol = $params;
            try {
                $this->selectFolder('INBOX');
            } catch (Exception\ExceptionInterface $e) {
                throw new Exception\RuntimeException('cannot select INBOX, is this a valid transport?', 0, $e);
            }
            return;
        }

        if (!isset($params->user)) {
            throw new Exception\InvalidArgumentException('need at least user in params');
        }

        $host     = isset($params->host)     ? $params->host     : 'localhost';
        $password = isset($params->password) ? $params->password : '';
        $port     = isset($params->port)     ? $params->port     : null;
        $ssl      = isset($params->ssl)      ? $params->ssl      : false;
		$logger   = isset($params->logger)   ? $params->logger   : null;

		$this->protocol = new ImapProtocol();

		if ($logger) {
			$this->protocol->setLogger($logger);

			$logger->logDebug(Arrays::implodeTemplate(array(
				'host'     => $host,
				'user'     => $params->user,
				'password' => 'xxxxxx',
				'port'     => $port,
				'ssl'      => $ssl
			), "[options] {KEY}: {VAL}\n"));
		}

		try {
			$this->protocol->connect($host, $port, $ssl);
			if ($logger) {
				$logger->logDebug("[protocol] connect okay");
			}
		} catch (Exception\RuntimeException $e) {
			if ($logger) {
				$logger->logError("[error:protocol] " . $e->getMessage());
			}
			$new_e = new Exception\RuntimeException('There was an error connecting to the server: ' . $e->getMessage(), self::ERR_CONNECT, $e);
			throw $new_e;
		}

		try {
			if (!$this->protocol->login($params->user, $password)) {
				$logger->logError("[error:protocol] login failed");
				$new_e = new Exception\RuntimeException('Your username or password is invalid', self::ERR_LOGIN);
				throw $new_e;
			}
			if ($logger) {
				$logger->logDebug("[protocol] login okay");
			}
		} catch (Exception\RuntimeException $e) {
			if ($logger) {
				$logger->logError("[error:protocol] " . $e->getMessage());
			}
			$new_e = new Exception\RuntimeException('Your username or password is invalid', self::ERR_LOGIN, $e);
			throw $new_e;
		}

        $this->selectFolder(isset($params->folder) ? $params->folder : 'INBOX');
    }


	/**
	 * @return ImapProtocol
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}


	/**
     * Count all unseen messages in mailbox
     *
     * @throws Exception\RuntimeException
     * @throws \Zend\Mail\Protocol\Exception\RuntimeException
     * @return int number of messages
     */
    public function countUnseenMessages()
    {
        if (!$this->currentFolder) {
            throw new Exception\RuntimeException('No selected folder to count');
        }

        $params = array('UNSEEN');
        return count($this->protocol->search($params));
    }


	/**
	 * Return array of unseen message UIDs.
	 *
	 * @return array|mixed
	 */
	public function getUnseenMessageUids()
	{
		$response = $this->protocol->requestAndResponse('UID SEARCH UNSEEN UNDELETED');
        if (!$response) {
            return $response;
        }

        foreach ($response as $ids) {
            if ($ids[0] == 'SEARCH') {
                array_shift($ids);
                return $ids;
            }
        }
        return array();
	}


	/**
	 * @param array $uids
	 * @return array
	 */
	public function getMessageSizesByUids(array $uids)
	{
		$data = $this->protocol->fetchByUid(array('UID', 'RFC822.SIZE'), $uids);

		$map = array();
		foreach ($data as $r) {
			$map[$r['UID']] = $r['RFC822.SIZE'];
		}

		return $map;
	}


	/**
	 * Gets email message in Message wrapper
	 *
	 * @param mixed $uid
	 * @return \Zend\Mail\Storage\Message
	 */
	public function getMessageByUid($uid)
    {
        $data = $this->protocol->fetchByUid(array('FLAGS', 'RFC822'), $uid);

        $flags = array();
        foreach ($data['FLAGS'] as $flag) {
            $flags[] = isset(static::$knownFlags[$flag]) ? static::$knownFlags[$flag] : $flag;
        }

        return new $this->messageClass(array(
			'handler' => $this,
			'id'      => $uid,
			'flags'   => $flags,
			'raw'     => $data['RFC822']
		));
    }


	/**
	 * Gets raw email message
	 *
	 * @param mixed $uid
	 * @return string
	 */
	public function getRawMessageByUid($uid)
	{
		$data = $this->protocol->fetchByUid(array('RFC822'), $uid);
		return $data;
	}


	/**
	 * Return just the headers of the message
	 *
	 * @param $uid
	 * @return string
	 */
	public function getMessageHeadersByUid($uid)
	{
		$data = $this->protocol->fetchByUid(array('RFC822.HEADER'), $uid);
		return $data;
	}


	/**
	 * Mark messages as 'seen'
	 *
	 * @param array $uids
	 */
	public function markReadByUids(array $uids)
	{
		return $this->protocol->storeById(array('\Seen'), $uids);
	}
}