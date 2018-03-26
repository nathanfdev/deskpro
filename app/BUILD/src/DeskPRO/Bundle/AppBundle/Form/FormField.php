<?php

namespace DeskPRO\Bundle\AppBundle\Form;

/**
 * Class FormField.
 */
class FormField
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param string $type
     * @param array  $options
     */
    public function __construct($type, array $options = [])
    {
        $this->type    = $type;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }
}
