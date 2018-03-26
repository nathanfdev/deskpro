<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

/**
 * Provides controller data access to the ticket labels.
 */
class TicketLabelsDataService extends AbstractDataService
{
    /**
     * Get the list of available ticket labels.
     *
     * @return string[]
     */
    public function getLabels()
    {
        /* So we're duplicating the labels per ticket. Hence we must do a group by operation
         * on the (hopefully) unique label names. That means using a querybuilder. */
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('l.label')
            ->from('DeskPRO:LabelTicket', 'l')
            ->groupBy('l.label')
            ->orderBy('l.label', 'ASC');

        $iterator = $qb->getQuery()->iterate();
        $labels   = [];
        foreach ($iterator as $data) {
            foreach ($data as $row) {
                $labels[] = $row['label'];
            }
        }

        return $labels;
    }
}
