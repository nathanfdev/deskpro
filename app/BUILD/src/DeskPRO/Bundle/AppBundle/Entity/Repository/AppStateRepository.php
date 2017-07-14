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

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppStoreBundle\Domain\Constants;
use Doctrine\ORM\EntityRepository;

/**
 * Class AppStateRepository.
 */
class AppStateRepository extends EntityRepository
{
    /**
     * @param AppInstance $instance
     * @param Person      $person
     * @param array       $names
     *
     * @return AppState[]
     */
    public function findReadableByName(AppInstance $instance, Person $person, array $names)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s')
            ->where(
                's.appInstance = :instance',
                '(s.owner = :owner OR s.permRead = :permRead)',
                's.name IN (:names)'
            )
            ->setParameter('instance', $instance)
            ->setParameter('permRead', Constants::PERMISSION_EVERYONE)
            ->setParameter('owner', $person)
            ->setParameter('names', $names)
            ->groupBy('s.name') // the could be more than one value for the name, get the first match
        ;

        return $qb->getQuery()->getResult();
    }
}
