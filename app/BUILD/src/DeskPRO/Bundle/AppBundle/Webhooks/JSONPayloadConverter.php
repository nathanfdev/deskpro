<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class JSONPayloadConverter implements PayloadConverter
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'json';
    }

    /**
     * @param WebhookRequest $request
     *
     * @throws WebhookException
     *
     * @return mixed
     */
    public function decode(WebhookRequest $request)
    {
        $body    = $request->getContent();
        $decoded = json_decode($body);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        throw new WebhookException('can not decode payload');
    }
}
