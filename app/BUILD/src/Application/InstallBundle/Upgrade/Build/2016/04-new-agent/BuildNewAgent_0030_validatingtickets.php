<?php

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
