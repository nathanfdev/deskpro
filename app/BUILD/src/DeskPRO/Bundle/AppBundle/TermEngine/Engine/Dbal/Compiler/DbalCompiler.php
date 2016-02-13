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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompositeQueryBuilder;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Psr\Log\LoggerInterface;

abstract class DbalCompiler implements DbalCompilerInterface
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory
     */
    protected $compiler_factory;

    /**
     * @var VisitorInterface[]
     */
    protected $visitors;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        DbalTermCompilerFactory $compiler_factory,
        array $visitors,
        LoggerInterface $logger
    ) {
        $this->compiler_factory = $compiler_factory;
        $this->visitors         = $visitors;
        $this->logger           = $logger;
    }

    /**
     * An opportunity for this engine implemention to alter the query before compile starts.
     *
     * @param DbalQueryBuilder $query_writer
     */
    abstract protected function enginePreCompile(DbalQueryBuilder $query_writer);

    /**
     * An opportunity for this engine implemention to alter the query after compile is completed.
     *
     * @param \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder $query_writer
     */
    abstract protected function enginePostCompile(DbalQueryBuilder $query_writer);

    /**
     * @param TermInterface $term
     *
     * @return DbalQuery
     */
    public function compile(TermInterface $term)
    {
        $timer = new SimpleTimer();

        // the fastest way to do this performance-wise is with reflection
        $ref             = new \ReflectionClass($term);
        $term_class_name = $ref->getShortName();

        $this->logger->info('DBAL TERM COMPILER START', array('term' => $term_class_name));

        // let visitors alter the term
        foreach ($this->visitors as $visitor) {
            $this->logger->debug('Passing term to visitor', array('visitor' => get_class($visitor)));
            $visitor->visit($term);
        }

        if (!count($this->visitors)) {
            $this->logger->debug('No visitors were registered, moving on');
        }

        $query_builder = new DbalQueryBuilder(new DbalQuery());

        // engine pre hook
        $this->enginePreCompile($query_builder);

        $this->compileTerm($term, $query_builder);

        // engine post hook
        $this->enginePostCompile($query_builder);

        $this->logger->info('DBAL TERM COMPILER END', array(
            'time' => $timer->getElapsedTime(),
        ));

        // result is a DbalQuery
        $query = $query_builder->getQuery();

        $this->logger->debug('DbalCompiler result', array('query' => $query));

        return $query;
    }

    public function getTermCompiler(TermInterface $term)
    {
        return $this->compiler_factory->getCompiler($term);
    }

    /**
     * @param TermInterface $term
     * @param $query_builder
     */
    protected function compileTerm(TermInterface $term, DbalQueryBuilder $query_builder)
    {
        if ($term instanceof CompositeTermInterface) {
            $timer = new SimpleTimer();

            $this->logger->debug(
                'START CompositeTerm',
                array(
                    'op' => $term->getOp(),
                )
            );

            // compile each term in the composite with a special DbalCompositeQueryBuilder instead
            // of the normal DbalQueryBuilder so we can catch the WHERE strings and process them before writing.
            $composite_query_builder = new DbalCompositeQueryBuilder($query_builder->getQuery());
            foreach ($term->getTerms() as $child_term) {
                $this->compileTerm($child_term, $composite_query_builder);
            }

            // filter the where strings, and put each inside their own parenthesis
            $where_strings = array();
            foreach ($composite_query_builder->getWhereStrings() as $where_string) {
                $where_string = trim($where_string);
                if ($where_string) {
                    $where_strings[] = sprintf('(%s)', $where_string);
                }
            }

            // now we can compose the proper WHERE string for this composite term
            $sep          = $term->getOp() === TermInterface::OP_OR ? 'OR' : 'AND';
            $where_string = implode(' '.$sep.' ', $where_strings);

            // write it in its own parenthesis
            $query_builder->setWhereString($where_string);

            $this->logger->debug(
                'END CompositeTerm',
                array(
                    'time' => $timer->getElapsedTime(),
                )
            );
        } else {

            // not a composite term, so compile it normally
            $this->compileSingleTerm($term, $query_builder);
        }
    }

    /**
     * @param TermInterface    $term
     * @param DbalQueryBuilder $query_builder
     */
    protected function compileSingleTerm(TermInterface $term, DbalQueryBuilder $query_builder)
    {
        // use term compilers to write the query and return the complete WHERE string
        $query_part = $this->getTermCompiler($term)->compile($term);
        $query_builder->writeQueryPart($query_part);
    }
}
