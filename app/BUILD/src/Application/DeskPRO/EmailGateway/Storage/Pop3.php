<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Storage;

use Application\DeskPRO\EmailGateway\Protocol\Pop3 as Pop3Protocol;
use Orb\Util\Arrays;
use Zend\Mail\Protocol\Exception;

class Pop3 extends \Zend\Mail\Storage\Pop3
{
    const ERR_CONNECT = 1;
    const ERR_LOGIN   = 2;

    /**
     * @var array
     */
    protected $capa_res = null;

    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object) $params;
        }

        $this->has['fetchPart'] = false;
        $this->has['top']       = null;
        $this->has['uniqueid']  = null;

        if ($params instanceof Pop3Protocol) {
            $this->protocol = $params;

            return;
        }

        $host      = isset($params->host) ? $params->host : 'localhost';
        $password  = isset($params->password) ? $params->password : '';
        $user      = isset($params->user) ? $params->user : '';
        $port      = isset($params->port) ? $params->port : null;
        $ssl       = isset($params->ssl) ? strtoupper($params->ssl) : false;
        $logger    = isset($params->logger) ? $params->logger : null;
        $test_mode = isset($params->test_mode) && $params->test_mode;

        $verifyCertificate = isset($params->disable_cert_validation) ? !$params->disable_cert_validation : true;

        $this->protocol = new Pop3Protocol();
        if ($logger) {
            $this->protocol->setLogger($logger);

            $logger->logDebug(Arrays::implodeTemplate([
                'host'     => $host,
                'user'     => $user,
                'password' => $test_mode ? $password : 'xxxxxx',
                'port'     => $port,
                'ssl'      => $ssl,
            ], "[options] {KEY}: {VAL}\n"));
        }

        try {
            $this->protocol->connect($host, $port, $ssl, $verifyCertificate);
            if ($logger) {
                $logger->logDebug('[protocol] connect okay');
            }
        } catch (Exception\RuntimeException $e) {
            if ($logger) {
                $logger->logError('[error:protocol] '.$e->getMessage());
            }
            $new_e = new Exception\RuntimeException('There was an error connecting to the server: '.$e->getMessage(), self::ERR_CONNECT, $e);
            throw $new_e;
        }

        try {
            $this->protocol->login($user, $password);
            if ($logger) {
                $logger->logDebug('[protocol] login okay');
            }
        } catch (Exception\RuntimeException $e) {
            if ($logger) {
                $logger->logError("[error:protocol] ({$e->getCode()}) ".$e->getMessage().' <'.get_class($e).'>');
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
