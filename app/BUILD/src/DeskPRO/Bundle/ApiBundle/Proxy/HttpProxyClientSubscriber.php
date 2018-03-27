<?php

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
