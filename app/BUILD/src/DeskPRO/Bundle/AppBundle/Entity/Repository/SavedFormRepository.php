<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use Doctrine\ORM\EntityRepository;

class SavedFormRepository extends EntityRepository
{
    /**
     * @param $external_code
     *
     * @return SavedForm|null
     */
    public function getByExternalCode($external_code)
    {
        $parsed = SavedForm::parseExternalCode($external_code);

        return $this->findOneBy(
            [
                'id'        => $parsed['id'],
                'auth_code' => $parsed['auth_code'],
            ]
        );
    }

    /**
     * @param $num_reminders
     * @param \DateTime $date_last_modified
     *
     * @return SavedForm[]
     */
    public function getForTicketReminders($num_reminders, \DateTime $min_date_created)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.num_sent_reminders = :num_reminders')->setParameter('num_reminders', $num_reminders);
        $qb->andWhere('s.date_created <= :date_created')->setParameter('date_created', $min_date_created);
        $qb->andWhere('s.data_type = :data_type')->setParameter('data_type', SavedForm::TYPE_NEW_TICKET);
        $qb->andWhere('s.intention_type = :intention_type')->setParameter('intention_type', SavedForm::INTENTION_VERIFY_EMAIL);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all saved forms for this person, plus some additional auth codes that we also want regardless of Person.
     *
     * @param Person $person
     * @param array  $external_codes
     *
     * @return array result array of SavedForm objects
     */
    public function getForPerson(Person $person, array $external_codes)
    {
        $auth_verification = [];
        $auth_codes        = [];

        foreach ($external_codes as $external_code) {
            $parsed                           = SavedForm::parseExternalCode($external_code);
            $auth_codes[]                     = $parsed['auth_code'];
            $auth_verification[$parsed['id']] = $parsed['auth_code'];
        }

        $query = $this->createQueryBuilder('s')
            ->where('s.person = :person OR s.auth_code IN (:auth_codes)')
            ->setParameter('person', $person)
            ->setParameter('auth_codes', $auth_codes)
            ->getQuery()
        ;

        $result = $query->getResult();

        // need to verify that the ID-AUTH_CODE external codes are valid
        $saved_forms = [];
        /** @var SavedForm $saved_form */
        foreach ($result as $saved_form) {
            if ($saved_form->getPerson() === $person) {
                // its for this person, so no need to check further
                $saved_forms[] = $saved_form;
            } elseif (array_key_exists($saved_form->getId(), $auth_verification)) {
                // its not for this person, so we do need to verify the auth code is correct
                if ($saved_form->getAuthCode() == $auth_verification[$saved_form->getId()]) {
                    $saved_forms[] = $saved_form;
                }
            }
        }

        return $saved_forms;
    }
}
