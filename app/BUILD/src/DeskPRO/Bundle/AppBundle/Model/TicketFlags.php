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

use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketFlagException;
use Doctrine\ORM\EntityManager;

/**
 * Pseudo-implementation of ticket flags. Those were hard-coded so this makes coupling a
 * little more loose.
 */
class TicketFlags
{
    protected $flags = array(
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
     * Get the list of available flags.
     *
     * @return array[string] the list of all ticket flag names
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Checks if a flag name is a valid ticket flag.
     *
     * @param string $flag_name is the ticket flag's name to be tested.
     *
     * @return bool
     */
    public function flagIsValid($flag_name)
    {
        return in_array($flag_name, $this->flags);
    }

    /**
     * Gets all the tickets matching a flag.
     *
     * @param int $person_id is the ID of the person whos has the flag.
     * @param int $flag_id   the flag ID.
     *
     * @throws UnknownTicketFlagException
     *
     * @return TicketFlagged[]
     */
    public function getAllRecordsForFlag($person_id, $flag_id)
    {
        if (!$flag = $this->getFlag($flag_id)) {
            throw new UnknownTicketFlagException();
        }

        return $this->getEm()->getRepository('DeskPRO:TicketFlagged')->findBy(array(
            'color'     => $flag,
            'person_id' => $person_id,
        ));
    }

    /**
     * @param int $id Flag id
     *
     * @return TicketFlagged|null
     */
    private function getFlag($id)
    {
        $id = (int) $id - 1;

        return isset($this->flags[$id]) ? $this->flags[$id] : null;
    }
}
