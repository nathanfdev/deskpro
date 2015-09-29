<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
 */
namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

class PhpMethod
{
    protected $code;
    protected $name;
    protected $arguments;
    protected $visibility;

    public function __construct()
    {
        $this->arguments  = array();
        $this->visibility = 'public';
    }

    public function __toString()
    {
        $name       = $this->getName();
        $visibility = $this->getVisibility();
        $code       = $this->getCode();
        $arguments  = $this->generateArgumentsString();

        return <<< PHPCODE
$visibility function $name($arguments)
{
    $code
}
PHPCODE;
    }

    /**
     * Not all arguments are strings.
     *
     * @param $default
     *
     * @return string
     */
    protected function getDefault($default)
    {
        if (
            is_int($default)
            || is_float($default)
            || in_array($default, array('null', 'array()', 'array', 'true', 'false'))
        ) {
            return $default;
        }

        return "'$default'"; // else make it a string
    }

    /**
     * @return string
     */
    protected function generateArgumentsString()
    {
        $args = array();
        foreach ($this->getArguments() as $arg) {
            $arg_string = '';
            if ($type = $arg['type']) {
                $arg_string .= $type.' ';
            }
            $arg_string .= '$'.$arg['name'];
            if ($default = $arg['default']) {
                $arg_string .= ' = '.$this->getDefault($default);
            }

            $args[] = $arg_string;
        }
        if (count($args) > 0) {
            $arguments = implode(', ', $args);
        } else {
            $arguments = '';
        }

        return $arguments;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function addArgument($name, $type = null, $default = null)
    {
        $this->arguments[$name] = array(
            'name'    => $name,
            'type'    => $type,
            'default' => $default,
        );
    }

    public function removeArgument($name)
    {
        if (array_key_exists($name, $this->arguments)) {
            unset($this->arguments[$name]);
        }
    }

    /**
     * @return mixed
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param mixed $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }
}
