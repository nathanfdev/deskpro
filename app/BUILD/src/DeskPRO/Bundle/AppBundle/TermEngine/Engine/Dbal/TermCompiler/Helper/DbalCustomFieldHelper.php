<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use Psr\Log\LoggerInterface;

class DbalCustomFieldHelper extends AbstractDbalHelper
{
    /** @var DbalJoinedHelper */
    protected $join_helper;

    public function __construct(LoggerInterface $logger, DbalJoinedHelper $join_helper)
    {
        parent::__construct($logger);
        $this->join_helper = $join_helper;
    }

    /**
     * An identifier for this helper.
     *
     * @return string
     */
    public function getId()
    {
        return 'custom_field';
    }

    /**
     * OP_IS:      field =        string1 OR  field =        string2
     * OP_NOT:     field !=       string1 AND field !=       string2
     * OP_HAS:     field LIKE     string1 OR  field LIKE     string2
     * OP_NOT_HAS: field NOT LIKE string1 AND field NOT LIKE string2.
     *
     * @param $field_id
     * @param $op
     * @param array $values
     *
     * @return DbalQueryPart
     */
    public function buildQueryPart($field_id, $op, array $values)
    {
        $part = $this->join_helper->buildQueryPart(
            [
                'custom_data_ticket.value' => array_values($values),
            ],
            'custom_data_ticket',
            'custom_data_ticket.ticket_id = ticket.id',
            $op
        );

        return $part;
    }
}
