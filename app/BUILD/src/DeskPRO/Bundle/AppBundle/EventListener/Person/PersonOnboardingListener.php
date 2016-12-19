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

namespace DeskPRO\Bundle\AppBundle\EventListener\Person;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PersonOnboardingListener
{
    /** @var ArrayCollection */
    private $newEntities;

    public function __construct()
    {
        $this->newEntities = new ArrayCollection();
    }

    protected static $onboardings = [
        'topbar',
    ];

    public function prePersist(Person $person)
    {
        if ($person->isAgent()) {
            $this->addOnboardings($person);
        }
    }

    public function preUpdate(Person $person, PreUpdateEventArgs $event)
    {
        if (!$event->hasChangedField('is_agent') || !$event->getNewValue('is_agent')) {
            return;
        }

        $this->addOnboardings($person);
    }

    protected function addOnboardings(Person $person)
    {
        foreach (self::$onboardings as $class) {
            if ($this->newEntities->containsKey($class.$person->getId())) {
                continue;
            }
            $onboarding = new PersonOnboarding();
            $onboarding->setPerson($person);
            $onboarding->setOnboardingClass($class);
            $onboarding->setApplication(PersonOnboarding::APPLICATION_AGENT);
            $this->newEntities->set($class.$person->getId(), $onboarding);
        }
    }

    public function preFlush(Person $person, PreFlushEventArgs $args)
    {
        if ($this->newEntities->count() > 0) {
            $em  = $args->getEntityManager();
            $uow = $em->getUnitOfWork();

            foreach ($this->newEntities as $entity) {
                $em->persist($entity);
                $uow->computeChangeSet($em->getClassMetadata(get_class($entity)), $entity);
            }

            $this->newEntities = new ArrayCollection(); // clear this to prevent recursive flushing
        }
    }
}
