<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class TmpDataListener
 *
 * @package DeskPRO\Bundle\AppBundle\EventListener\Doctrine
 */
class TmpDataListener
{
    /**
     * @param TmpData            $tmpData
     * @param LifecycleEventArgs $eventArgs
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function prePersist(TmpData $tmpData, LifecycleEventArgs $eventArgs)
    {
        $type = $tmpData->getData('_type');

        if ($type !== null && $type === 'reset-password') {
            /** @var \Application\DeskPRO\DBAL\Connection $db */
            /** @var \Doctrine\ORM\EntityManager $em */
            $em       = $eventArgs->getEntityManager();
            $db       = $em->getConnection();
            $personId = $tmpData->getData('person');

            $db->delete('sessions', ['person_id' => $personId]);
            $db->delete('sess_data', ['person_id' => $personId]);

            /** @var Person $person */
            $person = $em->getRepository(Person::class)->find($personId);

            if ($person) {
                /** @var \Application\DeskPRO\Entity\ApiToken $token */
                $token = $em
                    ->getRepository('DeskPRO:ApiToken')
                    ->getTokenForPerson($person);
                if ($token) {
                    $token->regenerateToken();
                    $em->persist($token);
                }
            }
        }
    }
}
