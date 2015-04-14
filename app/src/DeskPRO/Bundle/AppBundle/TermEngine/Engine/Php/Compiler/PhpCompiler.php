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
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpMethod;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\PhpTermCompilerFactory;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpClass;

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

    public function __construct(PhpTermCompilerFactory $term_compiler_factory, array $visitors)
    {
        $this->term_compiler_factory = $term_compiler_factory;
        $this->visitors = $visitors;
    }

    /**
     * An opportunity for this engine implemention to alter the query before compile starts
     *
     * @param PhpClass $php_class
     * @return void
     */
    abstract protected function enginePreCompile(PhpClass $php_class);

    /**
     * An opportunity for this engine implemention to alter the query after compile is completed
     *
     * @param PhpClass $php_class
     * @return void
     */
    abstract protected function enginePostCompile(PhpClass $php_class);

    public function compile(TermInterface $term)
    {
        // let visitors alter the term
        foreach ($this->visitors as $visitor) {
            $visitor->visit($term);
        }

        $php_class = new PhpClass();

        $this->enginePreCompile($php_class);

        $main_method = $this->compileTerm($term, $php_class);

        $main_method->setName('mainCheck');
        // we alter the mainCheck method's arguments in the engine postCompile hook
        $php_class->addMethod($main_method);

        $this->enginePostCompile($php_class);

        // use the term compiler to get a PhpTicketChecker
        // use it to create a PhpMethod

        // set that PhpMethod as the "main method" on the class by naming it
        // "check". Let the others be random names.

        // add our boiler plate "isTicketMatch" method to the class and call this main
        // method to get the final response value

        // return the PhpClass object

        return $php_class;
    }

    /**
     * @param TermInterface $term
     * @param PhpClass $php_class
     * @return PhpMethod
     */
    protected function compileTerm(TermInterface $term, PhpClass $php_class)
    {
        if ($term instanceof CompositeTermInterface) {
            $method_checks = array();
            $php_check = '';

            /** @var TermInterface $child_term */
            foreach ($term->getTerms() as $child_term) {
                $method = $this->compileTerm($child_term, $php_class);
                $method_checks[] = '$this->' . $php_class->addMethod($method) . '($ticket)';
            }

            $sep = (strtolower($term->getOp()) === strtolower(TermInterface::OP_AND)) ? ' && ' : ' || ';

            $php_check .= 'if (';
            $php_check .= implode($sep, $method_checks);
            $php_check .= ') { $check = true; }';

        } else {
            $php_check = $this->getTermCompiler($term)->compile($term);
        }

        $method_code = '$check = false; ' . $php_check . ' return $check;';

        $method = new PhpMethod();
        $method->addArgument('ticket', '\Application\DeskPRO\Entity\Ticket');
        $method->setCode($method_code);

        return $method;
    }

    protected function getTermCompiler(TermInterface $term)
    {
        return $this->term_compiler_factory->getCompiler($term);
    }
}
