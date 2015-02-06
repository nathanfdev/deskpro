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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepo;
use Doctrine\ORM\EntityManager;

class PersonDataService extends AbstractDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $email
     * @return Person|null
     */
    public function getPersonForEmail($email)
    {
        // using caution and not caching most PersonDataService methods
        return $this->getPersonRepo()->findOneByEmail($email);
    }

    public function getPersonForPasswordResetCode($code)
    {
        // using caution and not caching most PersonDataService methods
        if (strlen($code) > 0) {
            return $this->getPersonRepo()->findOneBy(array(
                'password_reset_code' => $code
            ));
        }

        return null;
    }

    /**
     * @return PersonRepo
     */
    public function getPersonRepo()
    {
        return $this->em->getRepository('DeskPRO:Person');
    }
}
