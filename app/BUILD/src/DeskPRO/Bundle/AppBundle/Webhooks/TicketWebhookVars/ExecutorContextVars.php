<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookException;
use Symfony\Component\PropertyAccess;

class ExecutorContextVars
{
    /**
     * @param ExecutorContextInterface $executionContext
     * @param string                   $name
     *
     * @return bool
     */
    public static function exists( ExecutorContextInterface $executionContext, $name)
    {
        $vars = ExecutorContextEnv::getTriggerVars($executionContext);
        if (empty($vars)) {
            return false;
        }

        $propertyAccessor = PropertyAccess\PropertyAccess::createPropertyAccessor();
        $propertyPath     = new PropertyAccess\PropertyPath($name);

        $accessorContext = new \stdClass();
        foreach ($vars as $name => $value) {
            $accessorContext->$name = $value;
        }
        return $propertyAccessor->isReadable($accessorContext, $propertyPath);
    }

    /**
     * @param ExecutorContextInterface $executionContext
     * @param string                   $name
     *
     * @throws WebhookException
     *
     * @return mixed
     */
    public static function get( ExecutorContextInterface $executionContext, $name)
    {
        $vars = ExecutorContextEnv::getTriggerVars($executionContext);

        $propertyAccessor = PropertyAccess\PropertyAccess::createPropertyAccessor();
        $propertyPath     = new PropertyAccess\PropertyPath($name);
        try {
            $accessorContext = new \stdClass();
            foreach ($vars as $name => $value) {
                $accessorContext->$name = $value;
            }
            return $propertyAccessor->getValue($accessorContext, $propertyPath);
        } catch (PropertyAccess\Exception\ExceptionInterface $e) {
            $msg = sprintf('can not retrieve value at path: %s', $name);
            throw new WebhookException($msg, 0, $e);
        }
    }
}
