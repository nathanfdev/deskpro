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
     * @var TermsHandlerInterface[]
     */
    private $handlers;

    /**
     * FieldId => Handler.
     *
     * @var TermsHandlerInterface[]
     */
    private $fieldToHandler;

    /**
     * The match ID is a concat of the operator and function name and field id.
     *
     * @var matchId => [handler, def]
     */
    private $matchFunctionMap;

    /**
     * TicketMatcher constructor.
     *
     * @param ValueResolver           $valueResolver
     * @param TermsHandlerInterface[] $handlers
     */
    public function __construct(ValueResolver $valueResolver, array $handlers)
    {
        $this->valueResovler = $valueResolver;
        $this->handlers      = $handlers;
        $this->logger        = new NullLogger();

        foreach ($handlers as $h) {
            foreach ($h->getHandledFields() as $fid) {
                if (!isset($this->fieldToHandler[$fid])) {
                    $this->fieldToHandler[$fid] = [];
                }
                $this->fieldToHandler[$fid][] = $h;
            }

            foreach ($h->getCompareFunctions() as $def) {
                $name = $def->getIdName();
                foreach ($def->fields as $field) {
                    $id                          = $name.'--'.$field;
                    $this->matchFunctionMap[$id] = [$h, $def];
                }
            }
        }

        $this->init();
    }

    protected function init()
    {
    }

    /**
     * @return ValueResolver
     */
    public function getValueResovler()
    {
        return $this->valueResovler;
    }

    /**
     * @return TermsHandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param $name
     * @param $fieldId
     *
     * @return string
     */
    public function getMatchFunctionForTerm(Term $term)
    {
        if ($term->options instanceof CompareOpt && $term->options->value instanceof FuncVal) {
            $name         = strtolower($term->options->value->name);
            $matchFuncId  = $name.'--'.$term->field->identity;
            $matchFuncAny = $name.'--*';

            $match = null;
            if (isset($this->matchFunctionMap[$matchFuncId])) {
                return $this->matchFunctionMap[$matchFuncId];
            } elseif (isset($this->matchFunctionMap[$matchFuncAny])) {
                return $this->matchFunctionMap[$matchFuncAny];
            }
        }

        return null;
    }

    /**
     * @param string $fieldId
     *
     * @return array|TermsHandlerInterface
     */
    public function getHandlersForFieldId($fieldId)
    {
        if (empty($this->fieldToHandler[$fieldId])) {
            return [];
        }

        return $this->fieldToHandler[$fieldId];
    }
}
