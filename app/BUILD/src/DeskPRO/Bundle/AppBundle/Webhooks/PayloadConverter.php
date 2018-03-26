<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

interface PayloadConverter
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param WebhookRequest $request
     *
     * @return mixed
     */
    public function decode(WebhookRequest $request);
}
