<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class WebhookHttpRequest implements WebhookRequest
{
    /** @var string */
    private $queryString;

    /** @var array|string[] */
    private $query;

    /** @var string|null */
    private $content;

    /** @var array|string[] */
    private $headers;

    /**
     * WebhookHttpRequest constructor.
     * @param $queryString
     * @param array|string[] $query
     * @param string|null $content
     * @param array|string[] $headers
     */
    public function __construct($queryString, array $query, $content, array $headers)
    {
        $this->queryString = $queryString;
        $this->query = $query;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @return array|string[]
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return array|string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
