<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityManager;

/**
 * this one is needed because there are too many legacy calls to flush in wrong places
 *
 * Class CustomDataPersister
 * @package Application\DeskPRO\CustomFields
 */
class CustomDataPersister
{
    protected $toAdd;

    protected $toRemove;

    public function __construct()
    {
        $this->toAdd    = array();
        $this->toRemove = array();
    }

    /**
     * @param DomainObject $entity
     */
    public function add(DomainObject $entity)
    {
        $this->toAdd[] = $entity;
    }

    /**
     * @param array $add
     */
    public function addArray(array $add)
    {
        foreach ($add as $_add) {
            $this->add($_add);
        }
    }

    /**
     * @param DomainObject $entity
     */
    public function remove(DomainObject $entity)
    {
        $this->toRemove[] = $entity;
    }

    /**
     * @param array $remove
     */
    public function removeArray(array $remove)
    {
        foreach ($remove as $_remove) {
            $this->remove($_remove);
        }
    }

    /**
     * @param EntityManager $em
     */
    public function flush(EntityManager $em)
    {
        foreach ($this->toAdd as $add) {
            $em->persist($add);
        }
        $this->toAdd = array();

        foreach ($this->toRemove as $remove) {
            $em->remove($remove);
        }
        $this->toRemove = array();

        $em->flush();
    }
}
