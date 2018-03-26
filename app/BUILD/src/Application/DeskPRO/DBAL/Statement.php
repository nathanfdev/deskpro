<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DBAL;

use Doctrine\DBAL\DBALException;

class Statement extends \Doctrine\DBAL\Statement
{
    public function execute($params = null)
    {
        $is_ignore = false;
        if ($this->sql == 'INSERT INTO labels_tickets (label, ticket_id) VALUES (?, ?)') {
            // easiest way to avoid problems with simultaneous labelling requests
            // no easy way to make doctrine do this on the entity-level,
            // and we dont want to resort to table locking.
            // future: add in official doctrine support for replace into/insert ignore?
            $this->sql = 'INSERT IGNORE INTO labels_tickets (label, ticket_id) VALUES (?, ?)';
            $is_ignore = true;
        }
        try {
            return parent::execute($params);
        } catch (\Exception $e) {
            if ($is_ignore) {
                return false;
            }
            if ($e instanceof DBALException || $e instanceof \PDOException) {
                $e->_dp_query        = $this->sql;
                $e->_dp_query_params = $params;
                throw $e;
            } else {
                throw $e;
            }
        }
    }
}
