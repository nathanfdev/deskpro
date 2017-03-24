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

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0030_validatingtickets extends AbstractBuild
{
    public function run()
    {
        $this->out('Resetting status on validating tickets');
        $db = $this->container->getDb();

        $label    = 'dp-was-validating';
        $set_data = [
            'hidden_status' => 'deleted',
            'date_status'   => date('Y-m-d H:i:s'),
        ];

        while ($ticket_ids = $this->loadNext()) {
            $label_batch = [];
            foreach ($ticket_ids as $tid) {
                $label_batch[] = ['ticket_id' => $tid, 'label' => $label];
            }

            $db->batchInsert('labels_tickets', $label_batch, true);
            $db->updateIn('tickets', $set_data, $ticket_ids);
        }
    }

    /**
     * @return int[]
     */
    private function loadNext()
    {
        $db         = $this->container->getDb();
        $ticket_ids = $db->fetchAllCol("
          SELECT id
          FROM tickets
          WHERE status = 'hidden' AND hidden_status = 'validating'
          LIMIT 250
        ");

        return $ticket_ids;
    }
}

//[[build:1460678413]]
