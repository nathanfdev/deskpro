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
