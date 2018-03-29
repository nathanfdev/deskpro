<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class WebhookException extends \Exception
{
    const CODE_PAYLOAD_FORBIDDEN = 101;

    const CODE_DECODER_NOT_FOUND = 102;

    /**
     * @param string $webhook
     * @param \Exception $prev
     * @return WebhookException
     */
    public static function payloadForbidden($webhook, \Exception $prev = null)
    {
        $msg = sprintf('webhook: %s does not accept a payload', $webhook);
        return new WebhookException($msg, WebhookException::CODE_PAYLOAD_FORBIDDEN, $prev);
    }

    /**
     * @param string $webhook
     * @param string $decoder
     * @param \Exception $prev
     * @return WebhookException
     */
    public static function decoderNotFound($webhook, $decoder, \Exception $prev = null)
    {
        $msg = sprintf('payload decoder with name: %s not found', $decoder);
        return new WebhookException($msg, WebhookException::CODE_DECODER_NOT_FOUND, $prev);
    }


}
