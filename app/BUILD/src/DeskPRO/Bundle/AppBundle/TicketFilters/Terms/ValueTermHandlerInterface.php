<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

/**
 * Interface ValueTermHandler.
 */
interface ValueTermHandlerInterface
{
    /**
     * @return HandlerDef
     */
    public function getValueHandlerDef();

    /**
     * @param string      $fieldId     The field the term is based on
     * @param string      $operator    The term operator
     * @param OptValue    $options     The value for the term
     * @param TicketModel $ticketModel The current ticket model
     * @param Context     $context     The current context
     * @param Term        $term        The raw term from which fieldId, operator, and options were read from
     *
     * @return bool
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term);

    /**
     * @param string      $name        The function name
     * @param string      $fieldId     The field the term is based on
     * @param string      $operator    The term operator
     * @param array       $params      The term params
     * @param TicketModel $ticketModel The current ticket model
     * @param Context     $context     The current context
     * @param Term        $term        The raw term from which fieldId, operator, and options were read from
     *
     * @return bool
     */
    public function doesTicketMatchFunc($name, $fieldId, $operator, array $params, TicketModel $ticketModel, Context $context, Term $term);
}
