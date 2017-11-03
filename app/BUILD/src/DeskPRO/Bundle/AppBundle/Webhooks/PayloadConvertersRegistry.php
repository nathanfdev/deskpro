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
