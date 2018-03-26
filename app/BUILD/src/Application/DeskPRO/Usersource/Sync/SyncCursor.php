<?php

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
