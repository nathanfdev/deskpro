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

namespace Application\DeskPRO\Net\Dns\Rdns;

class RdnsSocket implements RdnsInterface
{
    /**
     * @var int
     */
    private $timeout = 3;

    /**
     * @var string
     */
    private $dns_server;

    /**
     * @param string $dns_server
     * @param int $timeout
     */
    public function __construct($dns_server, $timeout = 3)
    {
        $this->dns_server = $dns_server;
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getDnsServer()
    {
        return $this->dns_server;
    }

    /**
     * @param string $dns_server
     */
    public function setDnsServer($dns_server)
    {
        $this->dns_server = $dns_server;
    }

    /**
     * @param string $ip
     * @return string|null
     * @throws \RuntimeException
     */
    public function lookup($ip)
    {
        return self::rdnsLookup($ip, $this->dns_server, $this->timeout);
    }

    /**
     * TODO: add support for ipv6 addresses
     *
     * @see http://www.php.net/manual/en/function.gethostbyaddr.php#46869
     * @param string $ip
     * @param string $dns_server
     * @param int $timeout
     * @return string|null
     */
    public static function rdnsLookup($ip, $dns_server, $timeout = 3)
    {
        $data = rand( 10, 77 ) . "\1\0\0\1\0\0\0\0\0\0";

        $bitso = array("","\1","\2","\3" );
        foreach( array_reverse( explode( '.', $ip ) ) as $bit ) {
            $l=strlen($bit);

            if (!isset($bitso[$l])) {
                return null;
            }

            $data.="{$bitso[$l]}".$bit;
        }

        $data .= "\7in-addr\4arpa\0\0\x0C\0\1";

        $errno = $errstr = 0;
        $fp = @fsockopen( "udp://{$dns_server}", 53, $errno, $errstr, $timeout );
        if (!$fp || !is_resource($fp)) {
            throw new \RuntimeException($errstr, $errno);
        }

        if(function_exists( 'socket_set_timeout' )) {
            socket_set_timeout($fp, $timeout);
        } elseif (function_exists('stream_set_timeout')) {
            stream_set_timeout($fp, $timeout);
        }

        $requestsize = @fwrite($fp, $data);
        $response = '';

        $start = time();
        while (((time() - $start) < $timeout) && ($buf = fread($fp, 512)) !== false) {
            $response .= $buf;
        }

        // hope we get a reply
        if (is_resource($fp)) {
            @fclose($fp);
        }

        // if empty response or bad response, return original ip
        if (empty($response) || bin2hex(substr($response, $requestsize + 2, 2)) != '000c') {
            return null;
        }

        $host = '';
        $loops = 0;

        // set our pointer at the beginning of the hostname uses the request size from earlier rather than work it out
        $pos = $requestsize + 12;
        do {
            // get segment size
            $len = unpack('c', substr($response, $pos, 1));

            // null terminated string, so length 0 = finished - return the hostname, without the trailing .
            if ($len[1] == 0) {
                return substr($host, 0, -1);
            }

            // add segment to our host
            $host .= substr($response, $pos + 1, $len[1]) . '.';

            // move pointer on to the next segment
            $pos += $len[1] + 1;

            // recursion protection
            $loops++;
        } while ($len[1] != 0 && $loops < 150);

        return null;
    }
}