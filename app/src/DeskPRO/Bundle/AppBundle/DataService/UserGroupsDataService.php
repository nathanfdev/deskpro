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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\EntityRepository\Person as PersonRepo;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class UserGroupsDataService extends AbstractDataService
{
    /**
     * @param mixed $person right now only ID is useful
     *
     * @return Person|null
     */
    public function loadAll()
    {
        return $this->getRepo()->findAll();
    }

    public function loadAllEnabled()
    {
        return $this->getRepo()->findAll(['is_enabled' => 1]);
    }

    /**
     * @param $email
     *
     * @return Person|null
     */
    public function getSingle($id)
    {
        // using caution and not caching most PersonDataService methods
        return $this->getRepo()->findOneBy(['id' => $id]);
    }

    public function getSingleEnabled($id)
    {
        return $this->getRepo()->findOneBy(['id' => $id, 'is_enabled' => 1]);
    }

    /**
     * @return PersonRepo
     */
    public function getRepo()
    {
        return $this->em->getRepository('DeskPRO:Usergroup');
    }
}
