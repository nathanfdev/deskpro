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
