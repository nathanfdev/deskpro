<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Orb\Util\CheckedOptionsArray;
use Orb\Util\OptionsArray;
use Orb\Util\Util;

/**
 * Base class for action defs. Each action still needs to implement
 * ActionInterface and/or MacroActionInterface interfaces.
 */
abstract class AbstractAction implements ActionDefinitionInterface
{
    /**
     * @var \Orb\Util\OptionsArray
     */
    private $options;

    /**
     * @var \Orb\Util\OptionsArray
     */
    private $meta;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->meta = new OptionsArray();
        $this->_initOptions($options);
    }

    /**
     * @return OptionsArray
     */
    public function getMetaData()
    {
        return $this->meta;
    }

    /**
     * @param array $options
     */
    private function _initOptions(array $options)
    {
        $this->options = $this->getOptionsDef();
        $this->options->setAll($options);
        $this->options->setArrayDefault($this->getDefaultOptions());
        $this->options->ensureRequired();
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [];
    }

    /**
     * @return CheckedOptionsArray
     */
    protected function getOptionsDef()
    {
        return new CheckedOptionsArray();
    }

    /**
     * Gets the type name of the criteria.
     *
     * @return string
     */
    public function getActionType()
    {
        return Util::getBaseClassname($this);
    }

    /**
     * Get's an array of options.
     *
     * @return CheckedOptionsArray
     */
    public function getActionOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getActionOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }
}
