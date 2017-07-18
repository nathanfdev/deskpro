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

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\Person as PersonRepo;

/**
 * Class PersonDataService.
 */
class PersonDataService extends AbstractDataService
{
    /**
     * @param mixed $person right now only ID is useful
     *
     * @return Person|null
     */
    public function getPerson($person)
    {
        return $this->getPersonRepo()->find($person);
    }

    /**
     * @param $email
     *
     * @return Person|null
     */
    public function getPersonForEmail($email)
    {
        // using caution and not caching most PersonDataService methods
        return $this->getPersonRepo()->findOneByEmail($email);
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function isPasswordResetReSendExpired(Person $person)
    {
        $tmpData = $this->em->getRepository(TmpData::class)->findOneBy([
            'name' => 'reset-password-'.$person->getId(),
        ]);

        return !$tmpData || new \DateTime('-1 hour') > $tmpData->getDateCreated();
    }

    /**
     * @param Person     $person
     * @param string|int $expire
     *
     * @return array
     */
    public function createPasswordReset(Person $person, $expire = null)
    {
        $name = 'reset-password-'.$person->getId();

        // only 1 valid at a time
        $this->em->getConnection()->delete('tmp_data', ['name' => $name]);

        $tmpData = TmpData::create('reset-password', ['person' => $person->getId()], $expire ?: '+1 day', $name);
        $this->em->persist($tmpData);
        $this->em->flush();

        return [
            'person'         => $person,
            'tmpdata'        => $tmpData,
            'code'           => $tmpData->getCode(),
            'date_requested' => $tmpData->getDateCreated(),
        ];
    }

    /**
     * @param string $code
     *
     * @return Person|null
     */
    public function findPasswordReset($code)
    {
        // using caution and not caching most PersonDataService methods
        if (strlen($code) > 0) {
            $tmpdata = $this->getTmpDataRepo()->getByCode($code, 'reset-password');
            if ($tmpdata) {
                $person = $this->getPersonRepo()->find($tmpdata->getData('person', 0));
                if ($person) {
                    return [
                        'person'         => $person,
                        'tmpdata'        => $tmpdata,
                        'code'           => $tmpdata->getCode(),
                        'date_requested' => $tmpdata->getDateCreated(),
                    ];
                }
            }
        }

        return;
    }

    /**
     * Clear a used password reset code.
     *
     * @param array $password_reset
     */
    public function clearPasswordReset(array $password_reset)
    {
        $this->em->remove($password_reset['tmpdata']);
        $this->em->flush();
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\TmpData
     */
    private function getTmpDataRepo()
    {
        return $this->em->getRepository('DeskPRO:TmpData');
    }

    /**
     * @return PersonRepo
     */
    public function getPersonRepo()
    {
        return $this->em->getRepository('DeskPRO:Person');
    }
}
