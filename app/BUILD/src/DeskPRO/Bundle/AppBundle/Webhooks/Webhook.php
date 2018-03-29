<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

interface Webhook
{
    /**
     * @return string
     */
    public function getPayloadConverter();
}
