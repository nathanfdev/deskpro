<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class WebhookInvocation
{
    /** @var array|string[] */
    private $query;

    /** @var string */
    private $queryString;

    /** @var array|string[] */
    private $headers;

    /** @var string */
    private $body;

    /** @var mixed */
    private $payload;

    /**
     * @param WebhookRequest $request
     * @param mixed $payload
     * @return WebhookInvocation
     */
    public static function fromRequestAndData(WebhookRequest $request, $payload = null)
    {
        return new WebhookInvocation(
            $request->getQuery(),
            $request->getQueryString(),
            $request->getHeaders(),
            $request->getContent(),
            $payload
        );
    }

    /**
     * @param array|string[] $query
     * @param string $queryString
     * @param array|string[] $headers
     * @param string $body
     * @param mixed $payload
     */
    public function __construct($query, $queryString, $headers, $body, $payload)
    {
        $this->query = $query;
        $this->queryString = $queryString;
        $this->headers = $headers;
        $this->body = $body;
        $this->payload = $payload;
    }

    /**
     * @return array|string[]
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->payload;
    }

    /**
     * @return array|string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function toPropertyMap()
    {
        return [
            'query' => $this->getQuery(),
            'queryString' => $this->getQueryString(),
            'request' => $this->getRequest(),
            'data' => $this->getData(),
            'headers' => $this->getHeaders(),
        ];
    }
}
