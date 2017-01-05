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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\PhpTermCompilerFactory;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(PhpTermCompilerFactory $term_compiler_factory, array $visitors, LoggerInterface $logger)
    {
        $this->term_compiler_factory = $term_compiler_factory;
        $this->visitors              = $visitors;
        $this->logger                = $logger;
    }

    /**
     * An opportunity for this engine implemention to alter the query after compile is completed.
     *
     * @param PhpCheck $php_class
     */
    abstract protected function enginePostCompile(PhpCheck $php_class);

    /**
     * @param TermInterface $term
     *
     * @return PhpCheck
     */
    public function compile(TermInterface $term)
    {
        $timer = new SimpleTimer();

        // the fastest way to do this performance-wise is with reflection
        $ref             = new \ReflectionClass($term);
        $term_class_name = $ref->getShortName();

        $this->logger->info('PHP TERM COMPILER START', ['term' => $term_class_name]);

        // let visitors alter the term
        foreach ($this->visitors as $visitor) {
            $this->logger->debug('Passing term to visitor', ['visitor' => get_class($visitor)]);
            $visitor->visit($term);
        }

        if (!count($this->visitors)) {
            $this->logger->debug('No visitors were registered, moving on');
        }

        $this->var_counter = 0; // resets for every compile

        $php_check = $this->compileTerm($term);

        $this->enginePostCompile($php_check);

        $this->logger->info(
            'PHP TERM COMPILER END',
            [
                'time' => $timer->getElapsedTime(),
            ]
        );

        $this->logger->debug('PhpCompiler result', ['php_check' => $php_check]);

        return $php_check;
    }

    /**
     * @param TermInterface $term
     *
     * @return PhpCheck
     */
    protected function compileTerm(TermInterface $term)
    {
        if ($term instanceof CompositeTermInterface) {
            $timer = new SimpleTimer();

            $this->logger->debug(
                'START CompositeTerm',
                [
                    'op' => $term->getOp(),
                ]
            );

            $parent_check          = new PhpCheck();
            $php_check_expressions = [];

            /** @var TermInterface $child_term */
            foreach ($term->getTerms() as $child_term) {
                $php_check = $this->compileTerm($child_term);
                $this->renameVars($php_check);
                foreach ($php_check->getVariables() as $name => $val) {
                    $parent_check->setVariable($name, $val);
                }

                $php_check_expressions[] = sprintf('(%s)', (string) $php_check);
            }

            $sep = (strtolower($term->getOp()) === strtolower(TermInterface::OP_AND)) ? ' && ' : ' || ';

            $parent_check->setExpression(sprintf('(%s)', implode($sep, $php_check_expressions)));

            $this->logger->debug(
                'END CompositeTerm',
                [
                    'time' => $timer->getElapsedTime(),
                ]
            );

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
