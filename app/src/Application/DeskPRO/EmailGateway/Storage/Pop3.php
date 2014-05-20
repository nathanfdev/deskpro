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

use Application\DeskPRO\EmailGateway\Protocol\Pop3 as Pop3Protocol;
use Orb\Util\Arrays;
use Zend\Mail\Protocol\Exception;

class Pop3 extends \Zend\Mail\Storage\Pop3
{
	const ERR_CONNECT = 1;
	const ERR_LOGIN = 2;

	/**
	 * @var array
	 */
	protected $capa_res = null;

	public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object)$params;
        }

        $this->has['fetchPart'] = false;
        $this->has['top']       = null;
        $this->has['uniqueid']  = null;

        if ($params instanceof Pop3Protocol) {
            $this->protocol = $params;
            return;
        }

        if (!isset($params->user)) {
            throw new Exception\InvalidArgumentException('need at least user in params');
        }

        $host     = isset($params->host)     ? $params->host     : 'localhost';
        $password = isset($params->password) ? $params->password : '';
        $port     = isset($params->port)     ? $params->port     : null;
        $ssl      = isset($params->ssl)      ? strtoupper($params->ssl) : false;
		$logger   = isset($params->logger)   ? $params->logger   : null;

        $this->protocol = new Pop3Protocol();
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
			$this->protocol->connect($host, $port, $ssl, $logger);
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
			$this->protocol->login($params->user, $password);
			if ($logger) {
				$logger->logDebug("[protocol] login okay");
			}
		} catch (Exception\RuntimeException $e) {
			if ($logger) {
				$logger->logError("[error:protocol] ({$e->getCode()}) " . $e->getMessage() . " <" . get_class($e) . ">");
			}
			$new_e = new Exception\RuntimeException('Your username or password is invalid', self::ERR_LOGIN, $e);
			throw $new_e;
		}
    }

	public function getProtocolCapabilities()
	{
		if ($this->capa_res !== null) {
			return $this->capa_res;
		}

		$this->capa_res = $this->getProtocol()->capa();
		$this->capa_res = Arrays::func($this->capa_res, 'trim');
		$this->capa_res = Arrays::removeFalsey($this->capa_res);

		return $this->capa_res;
	}

	public function canUniqueId()
	{
		return in_array('UIDL', $this->getProtocolCapabilities());
	}

	public function getProtocol()
	{
		return $this->protocol;
	}
}