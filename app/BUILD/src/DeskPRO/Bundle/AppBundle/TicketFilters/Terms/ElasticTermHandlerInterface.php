<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

/**
 * Interface ElasticTermHandler.
 */
interface ElasticTermHandlerInterface
{
    /**
     * @return HandlerDef
     */
    public function getElasticHandlerDef();

    /**
     * @param string   $fieldId  The field the term is based on
     * @param string   $operator The term operator
     * @param OptValue $options  The value for the term
     * @param Context  $context  The current context
     * @param Term     $term     The raw term from which fieldId, operator, and options were read from
     *
     * @return mixed
     */
    public function buildElasticCondition($fieldId, $operator, OptValue $options, Context $context, Term $term);

    /**
     * @param string  $name     The function name
     * @param string  $fieldId  The field the term is based on
     * @param string  $operator The term operator
     * @param array   $params   The term params
     * @param Context $context  The current context
     * @param Term    $term     The raw term from which fieldId, operator, and options were read from
     *
     * @return mixed
     */
    public function buildElasticFuncCondition($name, $fieldId, $operator, array $params, Context $context, Term $term);
}
