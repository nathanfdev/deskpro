<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Proxy;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Psr\Http\Message\RequestInterface;

class HttpProxyClientSubscriber extends Oauth1
{
    /** @var bool */
    private $signWithRsaKey = false;

    /** @var string|null */
    private $rsaKey = null;

    /**
     * Oauth1GuzzleSubscriber constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);
        if (
            $config['signature_method'] === Oauth1::SIGNATURE_METHOD_RSA
            && array_key_exists('private_key', $config)
        ) {
            $this->signWithRsaKey = true;
            $this->rsaKey         = $config['private_key'];
        }
    }

    public function getSignature(RequestInterface $request, array $params)
    {
        if ($this->signWithRsaKey) {
            return $this->getRsaSha1Signature($request, $params);
        }

        return parent::getSignature($request, $params);
    }

    private function getRsaSha1Signature(RequestInterface $request, array $params)
    {
        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        unset($params['oauth_signature']);

        // Add POST fields if the request uses POST fields and no files
        if ($request->getHeaderLine('Content-Type') == 'application/x-www-form-urlencoded') {
            $body = \GuzzleHttp\Psr7\parse_query($request->getBody()->getContents());
            $params += $body;
        }

        // Parse & add query string parameters as base string parameters
        $query = $request->getUri()->getQuery();
        $params += \GuzzleHttp\Psr7\parse_query($query);

        $baseString = $this->createBaseString($request, $this->prepareParameters($params));
        $signature  = '';
        openssl_sign($baseString, $signature, $this->rsaKey);

        return base64_encode($signature);
    }

    /**
     * Convert booleans to strings, removed unset parameters, and sorts the array.
     *
     * @param array $data Data array
     *
     * @return array
     */
    protected function prepareParameters($data)
    {
        // Parameters are sorted by name, using lexicographical byte value
        // ordering. Ref: Spec: 9.1.1 (1).
        uksort($data, 'strcmp');

        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
