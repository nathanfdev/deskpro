<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
            array(
                'getEmail',
                $person_email,
            ),
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
        //
        //return $this->generateAndCache(
        //    array(
        //        'getValidatingEmail',
        //        $validating_email_or_id,
        //    ),
        //    function () use ($that, $validating_email_or_id) {
        //        if (!$validating_email_or_id) { // we need some input
        //            return;
        //        }
        //
        //        if ($validating_email_or_id instanceof PersonEmailValidating) { // already have what you seek
        //            return $validating_email_or_id;
        //        }
        //
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
        //
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
