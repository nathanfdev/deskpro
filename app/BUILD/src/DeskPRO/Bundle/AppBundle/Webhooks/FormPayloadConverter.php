<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class FormPayloadConverter implements PayloadConverter
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'form';
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
        $decoded = [];
        if (mb_parse_str($body, $decoded)) {
            // we're converting the $decoded array into an \stdObject to use only property dot notation, children[0].firstName
            // otherwise for arrays we would have to use [children][0][firstName]
            return json_decode(json_encode($decoded));
        }

        throw new WebhookException('can not decode payload');
    }
}

