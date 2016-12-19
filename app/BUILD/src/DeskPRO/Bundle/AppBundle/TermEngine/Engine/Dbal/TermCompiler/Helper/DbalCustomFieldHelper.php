<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
