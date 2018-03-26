<?php

namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketStar;
use Doctrine\ORM\EntityManager;

/**
 * Pseudo-implementation of ticket stars. Those were hard-coded so this makes coupling a
 * little more loose.
 */
class TicketStars
{
    const CUSTOM_STAR_NAME_SETTING_PREFIX = 'agent.ui.flag.';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns ticket stars specific for current user.
     *
     * @param Person $person
     *
     * @return TicketStar[]
     */
    public function getTicketStars(Person $person)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('pref')
            ->from(PersonPref::class, 'pref')
            ->where('pref.name LIKE :name')
            ->andWhere('pref.person = :person')
            ->setParameter('name', self::CUSTOM_STAR_NAME_SETTING_PREFIX.'%')
            ->setParameter('person', $person)
        ;

        /** @var PersonPref[] $settings */
        $settings = $qb->getQuery()->getResult();
        $names    = [];

        foreach ($settings as $setting) {
            $starId         = str_replace(self::CUSTOM_STAR_NAME_SETTING_PREFIX, '', $setting->getName());
            $names[$starId] = $setting->getValueStr();
        }

        $stars = [];
        foreach (TicketFlagged::$colorMap as $starId => $color) {
            $starName = !empty($names[$color]) ? $names[$color] : $color;
            $stars[]  = new TicketStar($starId, $starName);
        }

        return $stars;
    }

    /**
     * @param Person $person
     * @param int    $color
     *
     * @return PersonSetting
     */
    public function findOrCreateStarNamePersonPref(Person $person, $color)
    {
        $name    = self::CUSTOM_STAR_NAME_SETTING_PREFIX.$color;
        $setting = $this->em->find(PersonPref::class, [
            'person' => $person,
            'name'   => $name,
        ]);

        if (!$setting) {
            $setting = new PersonPref();
            $setting
                ->setName($name)
                ->setPerson($person)
            ;
        }

        return $setting;
    }
}
