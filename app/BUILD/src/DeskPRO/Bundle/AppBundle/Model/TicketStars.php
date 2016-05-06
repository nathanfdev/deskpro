<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketFlagException;
use Doctrine\ORM\EntityManager;

/**
 * Pseudo-implementation of ticket stars. Those were hard-coded so this makes coupling a
 * little more loose.
 */
class TicketStars
{
    const CUSTOM_STAR_NAME_SETTING_PREFIX = 'agent.ticket_stars.name.';

    protected $stars = array(
        'blue',
        'green',
        'orange',
        'pink',
        'purple',
        'red',
        'yellow',
    );

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    protected function getEm()
    {
        return $this->em;
    }

    /**
     * Get the list of available stars.
     *
     * @return array[string] the list of all ticket star names
     */
    public function getStars()
    {
        return $this->stars;
    }

    /**
     * Checks if a star name is a valid ticket star.
     *
     * @param string $starName is the ticket star's name to be tested.
     *
     * @return bool
     */
    public function starIsValid($starName)
    {
        return in_array($starName, $this->stars);
    }

    /**
     * Gets all the tickets matching a star.
     *
     * @param int $personId is the ID of the person whos has the star.
     * @param int $starId   the star ID.
     *
     * @throws UnknownTicketFlagException
     *
     * @return TicketFlagged[]
     */
    public function getAllRecordsForStar($personId, $starId)
    {
        if (!$star = $this->getStar($starId)) {
            throw new UnknownTicketFlagException();
        }

        return $this->getEm()->getRepository(TicketFlagged::class)
            ->findBy(['color' => $star, 'person_id' => $personId]);
    }

    public function getCustomNames(Person $person)
    {
        // Retrieve custom stars name PersonalSetting instances
        $customNameSettings = $this->em->getRepository(PersonSetting::class)
            ->createQueryBuilder('ps')
            ->where('ps.name LIKE :name')
            ->andWhere('ps.person = :person')
            ->setParameter('name', self::CUSTOM_STAR_NAME_SETTING_PREFIX.'%')
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult();
        $customNames = [];

        /** @var PersonSetting $customNameSetting */
        foreach ($customNameSettings as $customNameSetting) {
            $starId = (int) str_replace(self::CUSTOM_STAR_NAME_SETTING_PREFIX, '', $customNameSetting->getName());

            $customNames[$starId] = $customNameSetting->getValue();
        }

        return $customNames;
    }

    /**
     * @param Person $person
     * @param int    $starId
     *
     * @return PersonSetting
     */
    public function findOrCreateStarNamePersonSetting(Person $person, $starId)
    {
        $settingName = self::CUSTOM_STAR_NAME_SETTING_PREFIX.$starId;

        $personSetting = $this->em->find(PersonSetting::class, ['person' => $person, 'name' => $settingName]);
        if (!$personSetting) {
            $personSetting = new PersonSetting($person, $settingName);
        }

        return $personSetting;
    }

    /**
     * @param Person $person
     * @param        $starId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function removeStarNamePersonSetting(Person $person, $starId)
    {
        $settingName = self::CUSTOM_STAR_NAME_SETTING_PREFIX.$starId;

        $personSetting = $this->em->find(PersonSetting::class, ['person' => $person, 'name' => $settingName]);
        if ($personSetting) {
            $this->em->remove($personSetting);
            $this->em->flush();
        }
    }

    /**
     * @param int $id Star id
     *
     * @return TicketFlagged|null
     */
    private function getStar($id)
    {
        $id = (int) $id - 1;

        return isset($this->stars[$id]) ? $this->stars[$id] : null;
    }
}
