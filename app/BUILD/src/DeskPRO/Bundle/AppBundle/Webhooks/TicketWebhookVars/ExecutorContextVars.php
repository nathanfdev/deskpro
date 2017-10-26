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

namespace DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookException;
use Symfony\Component\PropertyAccess;

class ExecutorContextVars
{
    /**
     * @param ExecutorContextInterface $context
     * @param string                   $name
     *
     * @return bool
     */
    public static function exists(ExecutorContextInterface $context, $name)
    {
        $payload = ExecutorContextEnv::getWebhookPayload($context);
        if (!$payload) {
            return false;
        }

        $propertyAccessor = PropertyAccess\PropertyAccess::createPropertyAccessor();
        $propertyPath     = new PropertyAccess\PropertyPath($name);

        return $propertyAccessor->isReadable($payload, $propertyPath);
    }

    /**
     * @param ExecutorContextInterface $context
     * @param string                   $name
     *
     * @throws WebhookException
     *
     * @return mixed
     */
    public static function get(ExecutorContextInterface $context, $name)
    {
        $payload = ExecutorContextEnv::getWebhookPayload($context);
        if (!$payload) {
            $msg = 'can not found webhook payload';
            throw new WebhookException($msg);
        }

        $propertyAccessor = PropertyAccess\PropertyAccess::createPropertyAccessor();
        $propertyPath     = new PropertyAccess\PropertyPath($name);
        try {
            return $propertyAccessor->getValue($payload, $propertyPath);
        } catch (PropertyAccess\Exception\ExceptionInterface $e) {
            $msg = sprintf('can not retrieve value at path: %s', $name);
            throw new WebhookException($msg, 0, $e);
        }
    }
}
