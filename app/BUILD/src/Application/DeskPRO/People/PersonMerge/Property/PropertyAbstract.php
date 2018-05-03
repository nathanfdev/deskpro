<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PersonMerge\Property;

use Application\DeskPRO\Entity\Person;

/**
 * A property is something that can be merged in a person.
 */
abstract class PropertyAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $other_person;

    /**
     * @var string
     */
    protected $strategy = null;

    /**
     * @var array
     */
    protected $strategy_options = [];

    const STRATEGY_LEFT    = 'left';
    const STRATEGY_RIGHT   = 'right';
    const STRATEGY_COMBINE = 'merge';

    public function __construct(Person $person, Person $other_person)
    {
        $this->person       = $person;
        $this->other_person = $other_person;
    }

    /**
     * Merge the two people.
     */
    abstract public function merge();

    /**
     * Set the merge strategy (how to handle conflicts).
     *
     * @param string $strategy
     */
    public function setStrategy($strategy, array $options = [])
    {
        $this->strategy = $strategy;
        $this->options  = $options;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * Get a strategy option.
     *
     * @param string $name    Name of the option
     * @param string $default The default value if it wasnt set
     *
     * @return mixed
     */
    public function getStrategyOption($name, $default = null)
    {
        return isset($this->strategy_options[$name]) ? $this->strategy_options[$name] : $default;
    }
}
