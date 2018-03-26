<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

interface WebhookRequest
{
    /**
     * @return string
     */
    public function getQueryString();

    /**
     * @return array|string[]
     */
    public function getQuery();

    /**
     * @return string
     */
    public function getContent();

    /**
     * @return array|string[]
     */
    public function getHeaders();
}
