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
