<?php

/**
 * Orb.
 */

namespace Orb\Sms;

/**
 * Interface SmsProviderInterface.
 *
 * An interface that defines an SMS Provider in the system. All SMS Providers that are used in this package
 * must implement this interface.
 */
interface SmsProviderInterface
{
    /**
     * @param string          $fromNumber  phone number to send to, provider should be able to handle any format
     * @param string          $toNumber    phone number, provider should be able to handle any format
     * @param SmsMessageChunk $textMessage the message chunk to be sent to the given number
     *
     * @throws \Orb\Sms\SmsException
     *
     * @return \Orb\Sms\SmsResult
     */
    public function sendMessage($toNumber, SmsMessageChunk $textMessage, $fromNumber);

    /**
     * A string identifier of the provider. This should be unique across the system.
     *
     * @return string
     */
    public function getName();

    /**
     * An array of parameters currently being used by this provider. Useful for serializing/deserializing a provider.
     * These values are usually sent to a factory along with the value of getName() to re-construct the provider.
     *
     * @return array
     */
    public function getParams();
}
