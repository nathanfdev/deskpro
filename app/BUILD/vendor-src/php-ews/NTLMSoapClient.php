<?php

/**
 * Contains NTLMSoapClient.
 */

/**
 * Soap Client using Microsoft's NTLM Authentication.
 *
 * Copyright (c) 2008 Invest-In-France Agency http://www.invest-in-france.org
 *
 * Author : Thomas Rabaix
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @link http://rabaix.net/en/articles/2008/03/13/using-soap-php-with-ntlm-authentication
 *
 * @author Thomas Rabaix
 */
class NTLMSoapClient extends SoapClient
{
    /**
     * cURL resource used to make the SOAP request.
     *
     * @var resource
     */
    protected $ch;

    /**
     * Whether or not to validate ssl certificates.
     *
     * @var bool
     */
    protected $validate = false;

    /**
     * @var string|null
     */
    private $preferred_http_auth = null;

    /**
     * Performs a SOAP request.
     *
     * @link http://php.net/manual/en/function.soap-soapclient-dorequest.php
     *
     * @param string $request  the xml soap request
     * @param string $location the url to request
     * @param string $action   the soap action.
     * @param int    $version  the soap version
     * @param int    $one_way
     *
     * @return string the xml soap response.
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $headers = array(
            'Method: POST',
            'Connection: Keep-Alive',
            'User-Agent: PHP-SOAP-CURL',
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "'.$action.'"',
        );

        // DESKPRO EDIT : Some versions of curl fail with some
        // values of CURLOPT_HTTPAUTH, so we try multiple times
        $user      = $this->user;
        $pass      = $this->password;
        $validate  = $this->validate;
        $make_curl = function ($httpauth) use ($location, $validate, $request, $headers, $user, $pass) {
            $ch = curl_init($location);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $validate);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $validate);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, $httpauth);
            curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);

            return $ch;
        };

        foreach (array($this->preferred_http_auth, CURLAUTH_NTLM, CURLAUTH_BASIC) as $httpauth) {
            if ($httpauth === null) {
                // first time this is run, preferred auth is unknown and will be null
                continue;
            }

            $this->ch = $make_curl($httpauth);
            $response = curl_exec($this->ch);
            $code     = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            // success type means we dont need to try others
            if ($code >= 200 && $code <= 399) {
                $this->preferred_http_auth = $httpauth;
                break;
            }
        }

        // If the response if false than there was an error and we should throw
        // an exception.
        if ($response === false) {
            throw new EWS_Exception(
                'Curl error: '.curl_error($this->ch),
                curl_errno($this->ch)
            );
        }

        $response = preg_replace('/(?!&#x0?(9|A|D))(&#x[0-1]?[0-9A-F];)/', ' ', $response);
        return $response;
    }

    /**
     * Sets whether or not to validate ssl certificates.
     *
     * @param bool $validate
     */
    public function validateCertificate($validate = true)
    {
        $this->validate = $validate;

        return true;
    }
}
