<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\RecordMapper;

use Application\ImportBundle\Entity\Ticket;
use Application\ImportBundle\Generator\Mapper\MapperInterface;

/**
 * Class TicketStatusRecordMapper
 * @package Application\ImportBundle\RecordMapper
 *
 * @deprecated used validator instead
 */
class TicketStatusRecordMapper implements RecordMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return MapperInterface::TYPE_TICKET_STATUS;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function findIdFromValue($value)
    {
        return false;
    }

    /**
     * @param $status
     * @return bool
     */
    public function isValidStatus($status)
    {
        $statuses = array(
            Ticket::STATUS_AWAITING_AGENT,
            Ticket::STATUS_AWAITING_USER,
            Ticket::STATUS_RESOLVED,
            Ticket::STATUS_ARCHIVED,
            Ticket::STATUS_HIDDEN,
        );

        return in_array($status, $statuses, true);
    }
}
