<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class TwitterAccount extends AbstractEntityRepository
{
    /** @var \Application\DeskPRO\Entity\TwitterAccount|null|bool */
    protected $_first = false;
    /** @var array */
    protected $_all;

    public function getAll()
    {
        if ($this->_all === null) {
            $this->_all = $this->getEntityManager()->createQuery('
                SELECT a, u
                FROM DeskPRO:TwitterAccount a INDEX BY a.id
                INNER JOIN a.user u
                ORDER BY u.name
            ')->execute();
        }

        return $this->_all;
    }

    public function getAllForPerson(\Application\DeskPRO\Entity\Person $person = null)
    {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        $output      = $this->getAll();
        $account_ids = $person->getTwitterAccountIds();
        foreach ($output as $key => $value) {
            if (!in_array($key, $account_ids)) {
                unset($output[$key]);
            }
        }

        return $output;
    }

    public function getFirst()
    {
        if ($this->_first === false) {
            $this->_first = $this->getEntityManager()->createQuery('
                SELECT a
                FROM DeskPRO:TwitterAccount a
                INNER JOIN a.user u
            ')->setMaxResults(1)->getOneOrNullResult();
        }

        return $this->_first;
    }
}
