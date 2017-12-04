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

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\FuncVal;

abstract class AbstractMatcher
{
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
