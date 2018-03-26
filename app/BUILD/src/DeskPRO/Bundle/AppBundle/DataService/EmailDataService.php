<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

class EmailDataService extends AbstractDataService
{
    /**
     * @param int|null|PersonEmail $person_email
     *
     * @return PersonEmail|null
     */
    public function getEmail($person_email)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getEmail',
                $person_email,
            ],
            function () use ($that, $person_email) {
                if (!$person_email) { // we need some input
                    return;
                }

                if ($person_email instanceof PersonEmail) { // already have what you seek
                    return $person_email;
                }

                return $that->getPersonEmailRepo()->find($person_email);
            }
        );
    }

    /**
     * @param $validating_email_or_id
     *
     * @todo no PersonEmailValidating anymore
     */
    public function getValidatingEmail($validating_email_or_id)
    {
        return;
        //$that = $this;

        //return $this->generateAndCache(
        //    array(
        //        'getValidatingEmail',
        //        $validating_email_or_id,
        //    ),
        //    function () use ($that, $validating_email_or_id) {
        //        if (!$validating_email_or_id) { // we need some input
        //            return;
        //        }

        //        if ($validating_email_or_id instanceof PersonEmailValidating) { // already have what you seek
        //            return $validating_email_or_id;
        //        }

        //        return $that->getPersonEmailValidatingRepo()->find($validating_email_or_id);
        //    }
        //);
    }

    /**
     * @param Person $person
     *
     * @todo no PersonEmailValidating anymore
     *
     * @return []
     */
    public function getValidatingEmails(Person $person)
    {
        return [];
        //$that = $this;

        //return $this->generateAndCache(
        //    array(
        //        'getValidatingEmails',
        //        $person,
        //    ),
        //    function () use ($that, $person) {
        //        return $that->getPersonEmailValidatingRepo()->getForPerson($person);
        //    }
        //);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\PersonEmail
     */
    public function getPersonEmailRepo()
    {
        return $this->em->getRepository('DeskPRO:PersonEmail');
    }

    /**
     * @internal
     *
     * @todo no PersonEmailValidating anymore
     */
    public function getPersonEmailValidatingRepo()
    {
        return;
    }
}
