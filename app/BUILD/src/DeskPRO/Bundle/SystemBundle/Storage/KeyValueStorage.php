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
namespace DeskPRO\Bundle\SystemBundle\Storage;

use DeskPRO\Bundle\SystemBundle\Entity\Storage\KeyValueEntry;
use Doctrine\ORM\EntityManager;

/**
 * Class KeyValueStorage.
 */
class KeyValueStorage implements KeyValueStorageInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * EventLogger constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function save($key, $value)
    {
        $entry = $this->em->find(KeyValueEntry::class, $key) ?: new KeyValueEntry($key);
        $entry->setValue($value);
        $this->em->persist($entry);
        $this->em->flush($entry);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return ($entry = $this->em->find(KeyValueEntry::class, $key)) ? $entry->getValue() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        if ($entry = $this->em->find(KeyValueEntry::class, $key)) {
            $this->em->remove($entry);
            $this->em->flush($entry);
        }
    }
}
