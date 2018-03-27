<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

/**
 * A PhpCheck is basically an expression with the ability to add variables to it.
 *
 * Your variables should be preceeed with a colon, like ":var".
 *
 * When you assign $php_check->setVariable('var', 'something'), then it will be
 * the variable used in the expression.
 *
 * See our docs for the full expression language you can use.
 */
class PhpCheck
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @var array
     */
    protected $variables;

    public function __construct($expression = '', array $variables = [])
    {
        $this->expression = $expression;
        $this->variables  = $variables;
    }

    public function __toString()
    {
        return trim($this->expression);
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param string $var_name
     * @param mixed  $value    - keep it to primitives: arrays, ints, strings
     */
    public function setVariable($var_name, $value)
    {
        $this->variables[$var_name] = $value;
    }

    public function renameVariable($old_name, $replace_name)
    {
        foreach ($this->variables as $name => $val) {
            if ($name == $old_name) {
                $this->variables[$replace_name] = $val;
                unset($this->variables[$old_name]);
            }
        }

        $this->expression = str_replace(':'.$old_name, ':'.$replace_name, $this->expression);
    }

    public function freezeVariableNames()
    {
        foreach ($this->variables as $name => $val) {
            $this->expression = str_replace(':'.$name, $name, $this->expression);
        }
    }
}
