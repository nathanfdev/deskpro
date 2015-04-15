<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpMethod;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\PhpTermCompilerFactory;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;

abstract class PhpCompiler
{
    /**
     * @var VisitorInterface[]
     */
    protected $visitors;

    /**
     * @var PhpTermCompilerFactory
     */
    protected $term_compiler_factory;

    /**
     * @var int
     */
    protected $var_counter;

    public function __construct(PhpTermCompilerFactory $term_compiler_factory, array $visitors)
    {
        $this->term_compiler_factory = $term_compiler_factory;
        $this->visitors = $visitors;
    }

    /**
     * An opportunity for this engine implemention to alter the query after compile is completed
     *
     * @param PhpCheck $php_class
     * @return void
     */
    abstract protected function enginePostCompile(PhpCheck $php_class);

    /**
     * @param TermInterface $term
     * @return PhpCheck
     */
    public function compile(TermInterface $term)
    {
        // let visitors alter the term
        foreach ($this->visitors as $visitor) {
            $visitor->visit($term);
        }

        $this->var_counter = 0;

        $php_check = $this->compileTerm($term);

        $this->enginePostCompile($php_check);

        return $php_check;
    }

    /**
     * @param TermInterface $term
     * @return PhpCheck
     */
    protected function compileTerm(TermInterface $term)
    {
        if ($term instanceof CompositeTermInterface) {
            $parent_check = new PhpCheck();
            $php_check_expressions = array();

            /** @var TermInterface $child_term */
            foreach ($term->getTerms() as $child_term) {
                $php_check = $this->compileTerm($child_term);
                $this->renameVars($php_check);
                foreach ($php_check->getVariables() as $name => $val) {
                    $parent_check->setVariable($name, $val);
                }

                $php_check_expressions[] = sprintf('(%s)', (string)$php_check);
            }

            $sep = (strtolower($term->getOp()) === strtolower(TermInterface::OP_AND)) ? ' && ' : ' || ';

            $parent_check->setExpression(sprintf('(%s)', implode($sep, $php_check_expressions)));

            return $parent_check;
        }

        $php_check = $this->getTermCompiler($term)->compile($term);
        $this->renameVars($php_check);

        return $php_check;
    }

    protected function renameVars(PhpCheck $check)
    {
        // just make sure all vars are unique, using a simple counter (var1, var2, etc)
        foreach ($check->getVariables() as $name => $val) {
            $check->renameVariable($name, sprintf('var%s', $this->var_counter++));
        }
    }

    protected function getTermCompiler(TermInterface $term)
    {
        return $this->term_compiler_factory->getCompiler($term);
    }
}
