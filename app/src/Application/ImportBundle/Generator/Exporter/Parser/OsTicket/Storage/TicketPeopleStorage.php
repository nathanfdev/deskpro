<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\OsTicket\Storage;

/**
 * OsTicket tickets people storage.
 *
 * Class TicketPeopleStorage
 */
class TicketPeopleStorage extends AbstractParserPeopleStorage
{
    /**
     * {@inheritdoc}
     */
    protected function getPeopleIds($data)
    {
        $people_ids = array();

        foreach ($data as $ticket) {
            if (isset($ticket['user_id']) && $ticket['user_id'] > 0) {
                $people_ids['user_'.$ticket['user_id']] = $ticket['user_id'];
            }
            if (isset($ticket['staff_id']) && $ticket['staff_id'] > 0) {
                $people_ids['staff_'.$ticket['staff_id']] = $ticket['staff_id'];
            }

            if (!empty($ticket['messages'])) {
                foreach ($ticket['messages'] as $message) {
                    if (isset($message['user_id']) && $message['user_id'] > 0) {
                        $people_ids['user_'.$message['user_id']] = $message['user_id'];
                    }
                    if (isset($message['staff_id']) && $message['staff_id'] > 0) {
                        $people_ids['staff_'.$message['staff_id']] = $message['staff_id'];
                    }
                }
            }
        }

        return array_unique($people_ids);
    }
}
