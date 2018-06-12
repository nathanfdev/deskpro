<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TermsHandlerInterface;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

abstract class AbstractMatcher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ValueResolver
     */
    private $valueResovler;

    /**
     * TicketMatcher constructor.
     *
     * @param ValueResolver $valueResolver
     * @param array         $handlers
     */
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResovler = $valueResolver;
        $this->logger        = new NullLogger();
    }

    /**
     * @return ValueResolver
     */
    public function getValueResovler()
    {
        return $this->valueResovler;
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
