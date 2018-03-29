<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Orb\Util\OptionsArray;

class ExecutorContextVars
{
    private $vars = [];

    private static function getVarFromContainer($name, $container)
    {
        if (is_array($container)) {
            return array_key_exists($name, $container) ? $container[$name] : null;
        }

        if ($container instanceof OptionsArray) {
            return $container->get($name, null);
        }

        if ($container instanceof ExecutorContextInterface) {
            return $container->getVars()->get($name, null);
        }

        return null;
    }

    public function setCustomFieldManager(\Application\DeskPRO\Service\CustomFieldManager $var)
    {
        $this->vars['custom_field_manager'] = $var;
    }

    /**
     * @param ExecutorContextInterface|array $context
     * @return \Application\DeskPRO\Service\CustomFieldManager|null
     */
    public static function getCustomFieldManagerFromContext($context)
    {
        $var = ExecutorContextVars::getVarFromContainer('custom_field_manager', $context);
        if (empty($var) || $var instanceof \Application\DeskPRO\Service\CustomFieldManager) {
            return $var;
        }

        throw new \RuntimeException('unexpected value for variable custom_field_manager');
    }

    /**
     * @return \Application\DeskPRO\Service\CustomFieldManager|null
     */
    public function getCustomFieldManager()
    {
        return ExecutorContextVars::getCustomFieldManagerFromContext($this->vars);
    }


    public function setTicketFieldManager(\Application\DeskPRO\CustomFields\TicketFieldManager $var)
    {
        $this->vars['ticket_field_manager'] = $var;
    }

    /**
     * @param ExecutorContextInterface|array $context
     * @return \Application\DeskPRO\CustomFields\TicketFieldManager|null
     */
    public static function getTicketFieldManagerFromContext( $context)
    {
        $var = ExecutorContextVars::getVarFromContainer('ticket_field_manager', $context);
        if (empty($var) || $var instanceof \Application\DeskPRO\CustomFields\TicketFieldManager) {
            return $var;
        }

        throw new \RuntimeException('unexpected value for variable ticket_field_manager');
    }

    public function configureContext(ExecutorContextInterface $context)
    {
        $context->getVars()->setArray($this->vars);
    }

    /**
     * Gets array of currently set context vars.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->vars;
    }

    /**
     * Set an auto context var. Added for compatibility with existing code.
     * Should not be used directly, always add a getter and setter for your variable
     *
     * @param array $vars
     */
    public function setAll(array $vars)
    {
        $this->vars = array_merge($this->vars, $vars);
    }

    /**
     * Set an auto context var. Added for compatibility with existing code.
     * Should not be used directly, always add a getter and setter for your variable
     *
     * @param string $k
     * @param mixed  $v
     */
    public function set($k, $v)
    {
        $this->vars[$k] = $v;
    }

    /**
     * Unset a context var. Added for compatibility with existing code
     *
     * @param string $k
     */
    public function unsetVar($k)
    {
        unset($this->vars[$k]);
    }
}
