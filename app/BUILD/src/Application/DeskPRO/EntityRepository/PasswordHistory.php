<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;

class PasswordHistory extends AbstractEntityRepository
{
    public function isUsedPassword(PersonEntity $person, $password)
    {
        $recs = $this->_em->getConnection()->fetchAll('
            SELECT password, password_scheme
            FROM password_history
            WHERE person_id = ?
        ', [$person->id]);

        if (!$recs) {
            return false;
        }

        foreach ($recs as $rec) {
            $scheme = App::getSystemObject('password_scheme', ['scheme' => $rec['password_scheme'] ?: 'deskpro4original']);
            if ($scheme->checkInput($password, $rec['password'])) {
                return true;
            }
        }

        return false;
    }
}
