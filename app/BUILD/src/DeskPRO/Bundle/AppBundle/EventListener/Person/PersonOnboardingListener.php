<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Person;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class PersonOnboardingListener.
 */
class PersonOnboardingListener
{
    protected static $onboardings = [
        'topbar',
    ];

    /**
     * @param Person $person
     */
    public function prePersist(Person $person)
    {
        if ($person->isAgent()) {
            $this->addOnboardings($person);
        }
    }

    /**
     * @param Person             $person
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Person $person, PreUpdateEventArgs $event)
    {
        if (!$event->hasChangedField('is_agent') || !$event->getNewValue('is_agent')) {
            return;
        }

        $this->addOnboardings($person);
    }

    /**
     * @param Person $person
     */
    protected function addOnboardings(Person $person)
    {
        foreach (self::$onboardings as $class) {
            $onboarding = new PersonOnboarding();
            $onboarding->setOnboardingClass($class);
            $onboarding->setApplication(PersonOnboarding::APPLICATION_AGENT);

            $person->addOnboarding($onboarding);
        }
    }
}
