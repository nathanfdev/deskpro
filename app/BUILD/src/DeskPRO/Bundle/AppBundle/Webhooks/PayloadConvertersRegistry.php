<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class PayloadConvertersRegistry
{
    /** @var array|PayloadConverter[] */
    private $convertersByName = [];

    /**
     * @param string $name
     *
     * @return PayloadConverter|null
     */
    public function lookupDecoderByName($name)
    {
        $converter = null;
        if (array_key_exists($name, $this->convertersByName)) {
            $converter = $this->convertersByName[$name];
        }

        if ($converter instanceof PayloadConverter) {
            return $converter;
        }

        return null;
    }

    /**
     * @param PayloadConverter $converter
     *
     * @throws WebhookException
     */
    public function addPayloadConverter(PayloadConverter $converter)
    {
        $name = $converter->getName();
        if (array_key_exists($name, $this->convertersByName)) {
            $msg = sprintf('converter with same name: %s already registered', $name);
            throw new WebhookException($msg);
        }

        $this->convertersByName[$name] = $converter;
    }
}
