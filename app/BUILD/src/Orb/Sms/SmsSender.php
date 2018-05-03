<?php

/**
 * Orb.
 */

namespace Orb\Sms;

/**
 * Responsible for sending SMS messages.
 *
 * The SmsSender can use any provider to send any message.
 *
 * If the same SmsProvider and/or From Number are used to send many messages, you
 * can set the default SmsProvider and From Number and omit them from the send methods
 * for your convenience.
 *
 * This default implementation sends the messages immediately and it does not depend on loggers, etc.
 * It is presumed that eventually we will subclass this to add functionality (ie. QueuingSmsSender).
 */
class SmsSender
{
    /**
     * @var SmsProviderInterface the default provider, used in the cases where the send methods get a null for Provider
     */
    protected $default_provider;

    /**
     * @var string the default From Number, used in the cases where the send methods get a null for From Number
     */
    protected $from_number;

    /**
     * @param SmsProviderInterface $provider    a quick way to set the default provider (optional)
     * @param string               $from_number a quick way to set the default from number (optional)
     */
    public function __construct(SmsProviderInterface $provider = null, $from_number = null)
    {
        $this->default_provider = $provider;
        $this->from_number      = $from_number;
    }

    /**
     * Send the $message to $to_number using the given $from_number and $provider.
     *
     * $from_number and $provider are optional, and fall back on the set default values.
     *
     * A $provider is needed (here or as a default) to send a message, but a From Number can be
     * null when sending a message if the provider does not need it.
     *
     * This method is meant to be overwritten by subclasses (different types of SmsSenders) to wrap their differences
     * around the doSend() method, which handles some common logic around interacting with the provider.
     *
     * @param string               $to_number
     * @param string               $message
     * @param string|null          $from_number
     * @param SmsProviderInterface $provider
     *
     * @throws SmsException
     */
    public function send($to_number, SmsMessage $message, $from_number = null, SmsProviderInterface $provider = null)
    {
        $this->doSend($to_number, $message, $from_number, $provider);
    }

    /**
     * @param $from_number
     *
     * @return string|null
     */
    protected function getFromNumber($from_number)
    {
        return $from_number ?: $this->getDefaultFromNumber();
    }

    /**
     * @param $provider
     *
     * @return SmsProviderInterface
     */
    protected function getProvider(SmsProviderInterface $provider = null)
    {
        return $provider ?: $this->getDefaultProvider();
    }

    /**
     * @param SmsProviderInterface $provider
     */
    public function setDefaultProvider(SmsProviderInterface $provider)
    {
        $this->default_provider = $provider;
    }

    /**
     * @return SmsProviderInterface
     */
    public function getDefaultProvider()
    {
        return $this->default_provider;
    }

    /**
     * @param string|null $from_number
     */
    public function setDefaultFromNumber($from_number)
    {
        $this->from_number = $from_number;
    }

    /**
     * @return string
     */
    public function getDefaultFromNumber()
    {
        return $this->from_number;
    }

    /**
     * This is called from within the send() method. It's arguments and return values are the same.
     *
     * doSend() will always take the $message, and split it into 160 character chunks and send those.
     *
     * This allows subclasses to reuse this sending logic, if they want, and wrap it with other functionality.
     *
     * @param string               $to_number
     * @param SmsMessage           $message
     * @param string|null          $from_number
     * @param SmsProviderInterface $provider
     *
     * @throws SmsException
     *
     * @return bool
     */
    protected function doSend(
        $to_number, SmsMessage $message, $from_number = null, SmsProviderInterface $provider = null
    ) {
        if (!$provider = $this->getProvider($provider)) {
            throw new SmsException('cannot send SMS without an SmsProvider');
        }

        $from = $this->getFromNumber($from_number);

        foreach ($message->getChunks() as $chunk) {
            $result = $provider->sendMessage($to_number, $chunk, $from);
            $chunk->setResult($result);
        }

        return $message->isSent();
    }
}
