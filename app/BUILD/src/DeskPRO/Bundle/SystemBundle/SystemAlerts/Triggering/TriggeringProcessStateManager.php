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
namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Storage\KeyValueStorageInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class TriggeringProcessStateManager.
 */
class TriggeringProcessStateManager
{
    const STORAGE_KEY = 'system_alerts.triggering_process_state';

    /**
     * @var KeyValueStorageInterface
     */
    private $storage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TriggeringProcessStateManager constructor.
     *
     * @param KeyValueStorageInterface $storage
     * @param EntityManager            $em
     */
    public function __construct(KeyValueStorageInterface $storage, EntityManager $em)
    {
        $this->storage = $storage;
        $this->em      = $em;
    }

    /**
     * Collects and saves internal state of the passed triggers.
     *
     * @param Trigger[] $triggers
     *
     * @throws \Exception
     */
    public function saveState(array $triggers)
    {
        $continuing_incidents = [];
        $trigger_states       = [];
        foreach ($triggers as $trigger) {
            if (!$trigger instanceof Trigger) {
                throw new \Exception('Expected a Trigger instance, got '.get_class($trigger) ?: gettype($trigger));
            }
            $trigger_states[get_class($trigger)] = $trigger->getState();

            if ($trigger instanceof StatefulIncidentTrigger && $trigger->hasContinuingIncident()) {
                $continuing_incidents[get_class($trigger)] = $trigger->getContinuingIncident()->getId();
            }
        }

        $this->storage->save(self::STORAGE_KEY, serialize([$trigger_states, $continuing_incidents]));
    }

    /**
     * Provides saved internal state to the passed triggers.
     *
     * @param Trigger[] $triggers
     *
     * @throws \Exception
     */
    public function provideState(array $triggers)
    {
        if (!$saved = $this->storage->get(self::STORAGE_KEY)) {
            return;
        }

        list($trigger_states, $continuing_incidents) = unserialize($saved);

        foreach ($triggers as $trigger) {
            if (!$trigger instanceof Trigger) {
                throw new \Exception('Expected a Trigger instance, got '.get_class($trigger) ?: gettype($trigger));
            }
            if (array_key_exists($class = get_class($trigger), $trigger_states)) {
                $trigger->setState($trigger_states[$class]);
            }
            if ($trigger instanceof StatefulIncidentTrigger && array_key_exists($class, $continuing_incidents)) {
                $trigger->setContinuingIncident(
                    $this->em->find(Incident::class, $continuing_incidents[$class])
                );
            }
        }
    }

    /**
     * Remove saved state.
     */
    public function remove()
    {
        $this->storage->remove(self::STORAGE_KEY);
    }
}
