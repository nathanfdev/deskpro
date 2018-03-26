<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Protocol;

use Orb\Log\Loggable;
use Orb\Log\Logger;
use Zend\Mail\Protocol\Exception;

class Pop3 extends \Zend\Mail\Protocol\Pop3 implements Loggable
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
     * @param null   $port
     * @param bool   $ssl
     * @param Logger $logger
     * @param int    $connect_timeout
     * @param int    $stream_timeout
     */
    public function __construct($host = '', $port = null, $ssl = false, Logger $logger = null, $connect_timeout = 8, $stream_timeout = 15)
    {
        $this->logger          = $logger;
        $this->connect_timeout = $connect_timeout;
        $this->stream_timeout  = $stream_timeout;
        parent::__construct($host, $port, $ssl = $ssl ? strtoupper($ssl) : $ssl);
    }

    /**
     * @param string $host
     * @param null   $port
     * @param bool   $ssl
     * @param bool   $verifyCertificate
     *
     * @throws \Zend\Mail\Protocol\Exception\RuntimeException
     *
     * @return string
     */
    public function connect($host, $port = null, $ssl = false, $verifyCertificate = true)
    {
        $ssl = $ssl ? strtoupper($ssl) : $ssl;

        switch ($ssl) {
            case 'SSL':
                $host    = 'ssl://'.$host;
                $wrapper = 'ssl';
                break;
            case 'TLS':
                $host    = 'tls://'.$host;
                $wrapper = 'ssl';
                break;
            default:
                $wrapper = 'tcp';
        }

        if (!$port) {
            $port = $ssl == 'SSL' ? 995 : 110;
        }

        $context = null;
        if (!$verifyCertificate) {
            $context = stream_context_create([
                $wrapper => [
                    'verify_peer'      => $verifyCertificate,
                    'verify_peer_name' => $verifyCertificate,
                ],
            ]);
        }

        $errno  = 0;
        $errstr = '';
        if ($context) {
            $this->socket = @stream_socket_client(
                $host.':'.$port,
                $errno,
                $errstr,
                $this->stream_timeout,
                \STREAM_CLIENT_CONNECT,
                $context
            );
        } else {
            $this->socket = @stream_socket_client(
                $host.':'.$port,
                $errno,
                $errstr,
                $this->stream_timeout,
                \STREAM_CLIENT_CONNECT
            );
        }
        if (!$this->socket) {
            throw new Exception\RuntimeException('cannot connect to host; error = '.$errstr.' (errno = '.$errno.' )');
        }

        $welcome = $this->readResponse();

        strtok($welcome, '<');
        $this->timestamp = strtok('>');
        if (!strpos($this->timestamp, '@')) {
            $this->timestamp = null;
        } else {
            $this->timestamp = '<'.$this->timestamp.'>';
        }

        if ($ssl === 'TLS') {
            $this->request('STLS');
            $result = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$result) {
                throw new Exception\RuntimeException('cannot enable TLS');
            }
        }

        return $welcome;
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
     * Make a RETR call for retrieving a full message with headers and body.
     *
     * @param int $msgno message number
     *
     * @return string message
     */
    public function retrieveToStream($msgno, $stream)
    {
        $result = $this->requestToStream("RETR $msgno", $stream);

        return $result;
    }

    /**
     * Send request and get resposne.
     *
     * @see sendRequest(), readResponse()
     *
     * @param string   $request request
     * @param resource $stream  stream
     *
     * @return int Number of bytes read to stream
     */
    public function requestToStream($request, $stream)
    {
        $this->sendRequest($request);

        return $this->readResponseToStream($stream);
    }

    /**
     * @param string $request
     */
    public function sendRequest($request)
    {
        if ($this->logger) {
            if (strpos($request, 'PASS ') === 0) {
                $this->logger->logDebug('==> PASS xxxxxx');
            } else {
                $this->logger->logDebug('==> '.$request);
            }
        }

        return parent::sendRequest($request);
    }

    /**
     * @param bool $multiline
     *
     * @throws \Zend\Mail\Protocol\Exception\RuntimeException
     *
     * @return string
     */
    public function readResponse($multiline = false)
    {
        $result = @fgets($this->socket);
        if (!is_string($result)) {
            if ($this->logger) {
                $this->logger->logDebug('<== read failed - connection closed?');
            }
            throw new Exception\RuntimeException('read failed - connection closed?');
        }

        $result = trim($result);
        if (strpos($result, ' ')) {
            list($status, $message) = explode(' ', $result, 2);
        } else {
            $status  = $result;
            $message = '';
        }

        if ($status != '+OK') {
            if ($this->logger) {
                $this->logger->logDebug("<== $status");
            }
            throw new Exception\RuntimeException('last request failed');
        }

        if ($multiline) {
            $message = '';
            $line    = fgets($this->socket);
            $log_msg = '';
            while ($line && rtrim($line, "\r\n") != '.') {
                if ($line[0] == '.') {
                    $line = substr($line, 1);
                }
                $message .= $line;
                $line = fgets($this->socket);
                if ($this->logger && !isset($log_msg[350])) {
                    $log_msg .= $line;
                }
            }
            if ($this->logger) {
                $this->logger->logDebug("<== $status $log_msg");
            }
        }

        return $message;
    }

    /**
     * This reads a multi-line response to a stream and returns the number of bytes read.
     *
     * @param $stream
     *
     * @throws \Zend\Mail\Protocol\Exception\RuntimeException
     *
     * @return int
     */
    public function readResponseToStream($stream)
    {
        $result = @fgets($this->socket);
        if (!is_string($result)) {
            if ($this->logger) {
                $this->logger->logDebug('<== read failed - connection closed?');
            }
            throw new Exception\RuntimeException('read failed - connection closed?');
        }

        $result = trim($result);
        if (strpos($result, ' ')) {
            list($status) = explode(' ', $result, 2);
        } else {
            $status = $result;
        }

        if ($status != '+OK') {
            if ($this->logger) {
                $this->logger->logDebug("<== $status");
            }
            throw new Exception\RuntimeException('last request failed');
        }

        $bytes   = 0;
        $line    = fgets($this->socket);
        $log_msg = '';
        while ($line && rtrim($line, "\r\n") != '.') {
            if ($line[0] == '.') {
                $line = substr($line, 1);
            }
            $bytes += fwrite($stream, $line);
            $line = fgets($this->socket);
            if ($this->logger && !isset($log_msg[350])) {
                $log_msg .= $line;
            }
        }
        if ($this->logger) {
            $this->logger->logDebug("<== $status $log_msg");
        }

        return $bytes;
    }
}
