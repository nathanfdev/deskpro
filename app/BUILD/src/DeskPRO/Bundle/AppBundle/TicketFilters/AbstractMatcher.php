<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Class AbstractMatcher.
 */
abstract class AbstractMatcher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ValueResolver
     */
    protected $valueResolver;

    /**
     * TicketMatcher constructor.
     *
     * @param ValueResolver $valueResolver
     */
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->logger        = new NullLogger();
    }

    /**
     * @return ValueResolver
     */
    public function getValueResolver()
    {
        return $this->valueResolver;
    }

    /**
     * @param Term $term
     *
     * @return bool
     */
    protected function isFunctionTerm(Term $term)
    {
        if ($term->options instanceof CompareOpt && $term->options->value instanceof FuncVal) {
            return true;
        }

        return false;
    }
}
