<?php

namespace Application\DeskPRO\People\PersonMerge;

use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

/**
 * - Backup Person and OtherPerson before merge
 * - Can restore persons from backup.
 */
class MergeBackup
{
    /**
     * @var Backup\PersonDump
     */
    protected $personDump;

    /**
     * @var Backup\PersonRestore
     */
    protected $personRestore;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager        $em
     * @param Backup\PersonDump    $personDump
     * @param Backup\PersonRestore $personRestore
     */
    public function __construct(EntityManager $em, Backup\PersonDump $personDump, Backup\PersonRestore $personRestore)
    {
        $this->em            = $em;
        $this->personDump    = $personDump;
        $this->personRestore = $personRestore;
    }

    /**
     * Return backup Id.
     *
     * @param Person $person
     * @param Person $otherPerson
     *
     * @return int
     */
    public function backup(Person $person, Person $otherPerson)
    {
        try {
            $personData      = $this->personDump->dump($person, false);
            $otherPersonData = $this->personDump->dump($otherPerson, true);
        } catch (Backup\DumpLimitException $ex) {
            //@TODO: log
            return null;
        }

        $dataStore = new DataStore();
        $dataStore->setType('person_merge_backup');
        $dataStore->setName(sprintf('person_merge_backup_%s_%s', $person->getId(), $otherPerson->getId()));
        $dataStore->setData('person', $personData);
        $dataStore->setData('other_person', $otherPersonData);

        $this->em->persist($dataStore);
        $this->em->flush($dataStore);

        return $dataStore->getId();
    }

    /**
     * @param Person $person
     * @param Person $otherPerson
     * @param int    $backupId
     *
     * @throws \Exception
     */
    public function restore(Person $person, Person $otherPerson, $backupId)
    {
        $dataStore = $this->em->getRepository(DataStore::class)->find($backupId);
        if (!$dataStore) {
            throw new \Exception(sprintf('[MergeBackup] Can\'t find Person Merge backup by id: %s', $backupId));
        }

        $this->personRestore->restore($person, $dataStore->getData('person', []));
        $this->personRestore->restore($otherPerson, $dataStore->getData('other_person', []));
    }

    /**
     * @param int $backupId
     */
    public function remove($backupId)
    {
        $dataStore = $this->em->getRepository(DataStore::class)->find($backupId);
        if ($dataStore) {
            $this->em->remove($dataStore);
            $this->em->flush($dataStore);
        }
    }
}
