<?php

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

/**
 * Class DbalCompiler.
 */
abstract class DbalCompiler implements DbalCompilerInterface
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory
     */
    protected $compilerFactory;

    /**
     * @var VisitorInterface[]
     */
    protected $visitors;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param DbalTermCompilerFactory $compilerFactory
     * @param array                   $visitors
     * @param LoggerInterface         $logger
     */
    public function __construct(DbalTermCompilerFactory $compilerFactory, array $visitors, LoggerInterface $logger)
    {
        $this->compilerFactory = $compilerFactory;
        $this->visitors        = $visitors;
        $this->logger          = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(TermInterface $term)
    {
        $timer = new SimpleTimer();

        // the fastest way to do this performance-wise is with reflection
        $ref           = new \ReflectionClass($term);
        $termClassName = $ref->getShortName();

        $this->logger->info('DBAL TERM COMPILER START', ['term' => $termClassName]);

        // let visitors alter the term
        foreach ($this->visitors as $visitor) {
            $this->logger->debug('Passing term to visitor', ['visitor' => get_class($visitor)]);
            $visitor->visit($term);
        }

        if (!count($this->visitors)) {
            $this->logger->debug('No visitors were registered, moving on');
        }

        $qb = new DbalQueryBuilder(new DbalQuery());

        // engine pre hook
        $this->enginePreCompile($qb);

        $this->compileTerm($term, $qb);

        // engine post hook
        $this->enginePostCompile($qb);

        $this->logger->info('DBAL TERM COMPILER END', [
            'time' => $timer->getElapsedTime(),
        ]);

        // result is a DbalQuery
        $query = $qb->getQuery();
        $this->logger->debug('DbalCompiler result', ['query' => $query]);

        return $query;
    }

    /**
     * @param TermInterface $term
     *
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler
     */
    public function getTermCompiler(TermInterface $term)
    {
        return $this->compilerFactory->getCompiler($term);
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
     * @param $qb
     */
    protected function compileTerm(TermInterface $term, DbalQueryBuilder $qb)
    {
        if ($term instanceof CompositeTermInterface) {
            $timer = new SimpleTimer();

            $this->logger->debug('START CompositeTerm', [
                'op' => $term->getOp(),
            ]);

            // compile each term in the composite with a special DbalCompositeQueryBuilder instead
            // of the normal DbalQueryBuilder so we can catch the WHERE strings and process them before writing.
            $compositeQb = new DbalCompositeQueryBuilder($qb->getQuery());
            foreach ($term->getTerms() as $child_term) {
                $this->compileTerm($child_term, $compositeQb);
            }

            // filter the where strings, and put each inside their own parenthesis
            $where_strings = [];
            foreach ($compositeQb->getWhereStrings() as $whereString) {
                $whereString = trim($whereString);
                if ($whereString) {
                    $where_strings[] = sprintf('(%s)', $whereString);
                }
            }

            // now we can compose the proper WHERE string for this composite term
            $sep         = $term->getOp() === TermInterface::OP_OR ? 'OR' : 'AND';
            $whereString = implode(' '.$sep.' ', $where_strings);

            // write it in its own parenthesis
            $qb->setWhereString($whereString);

            $this->logger->debug('END CompositeTerm', [
                'time' => $timer->getElapsedTime(),
            ]);
        } else {

            // not a composite term, so compile it normally
            $this->compileSingleTerm($term, $qb);
        }
    }

    /**
     * Use term compilers to write the query and return the complete WHERE string.
     *
     * @param TermInterface    $term
     * @param DbalQueryBuilder $qb
     */
    protected function compileSingleTerm(TermInterface $term, DbalQueryBuilder $qb)
    {
        $qb->writeQueryPart($this->getTermCompiler($term)->compile($term));
    }
}
