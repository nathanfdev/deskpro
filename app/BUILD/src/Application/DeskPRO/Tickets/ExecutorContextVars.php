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
