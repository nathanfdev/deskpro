<?php

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
     * @param AppInstance|string $instance
     * @param Person             $person
     * @param string              $name
     *
     * @return AppState|null
     */
    public function findOneReadableByName($instance, Person $person, $name)
    {
        $results = $this->findReadableByName($instance, $person, [ $name ]);
        if (count($results) === 1) {
            return array_pop($results);
        }

        return null;
    }

    /**
     * @param AppInstance|string $instance
     * @param Person             $person
     * @param array              $names
     *
     * @return AppState[]
     */
    public function findReadableByName($instance, Person $person, array $names)
    {
        $instanceId = null;
        if ($instance instanceof AppInstance) {
            $instanceId = $instance->getId();
        } else if (is_string($instance) && !empty($instance)) {
            $instanceId = $instance;
        }

        if (empty($instanceId)) {
            throw new \BadMethodCallException('Parameter instance must be a valid AppInstance id or an instance of AppInstance');
        }

        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s')
            ->where(
                's.appInstance = :instance',
                '(s.owner = :owner OR s.permRead = :permRead)',
                's.name IN (:names)'
            )
            ->setParameter('instance', $instanceId)
            ->setParameter('permRead', Constants::PERMISSION_EVERYONE)
            ->setParameter('owner', $person)
            ->setParameter('names', $names)
            ->groupBy('s.name') // the could be more than one value for the name, get the first match
        ;

        return $qb->getQuery()->getResult();
    }
}
