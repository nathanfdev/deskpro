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

namespace Application\DeskPRO\Usersource\Sync;

/**
 * Keeps a "bookmark" on the syncer's progress so that it can be paused and resumed later.
 */
class SyncCursor
{
    /**
     * @var int this is a straight int counter, starting at 1, keeping track of where in the "list" we are
     */
    private $location;

    /**
     * @var int unlike location, this is not just used internally, this is used to report on the log
     *          make sure it always reflects how many records have actually been updated
     */
    private $counter;

    /**
     * @var int some syncers do more than 1 pass on the data, do this is a way to keep track of that
     */
    private $phase;

    /**
     * @var bool set this to true if the usersource is completely done, it defaults to false meaning
     *           we still have more user info to process starting at $location
     */
    private $completed;

    public function __construct($location = 1, $counter = 0, $phase = 1)
    {
        $this->setLocation($location);
        $this->setPhase($phase);
        $this->setCounter($counter);
    }

    public function incrementLocation()
    {
        ++$this->location;
    }

    public function incrementCounter()
    {
        ++$this->counter;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = (int) $location;
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     */
    public function setCounter($counter)
    {
        $this->counter = (int) $counter;
    }

    /**
     * @return int
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * @param int $phase
     */
    public function setPhase($phase)
    {
        $this->phase = (int) $phase;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    public function markCompleted()
    {
        $this->completed = true;
    }
}
