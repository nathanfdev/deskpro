<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EventDispatcher;

/**
 * An object wrapper that will fire a callback when the property changed method
 * is called (used for doctrine PropertyChagned).
 */
class PropertyChangedCallback implements \Doctrine\Common\PropertyChangedListener
{
    /** @var callable */
    protected $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Notifies the listener of a property change.
     *
     * @param object $sender       The object on which the property changed
     * @param string $propertyName The name of the property that changed
     * @param mixed  $oldValue     The old value of the property that changed
     * @param mixed  $newValue     The new value of the property that changed
     */
    public function propertyChanged($sender, $propertyName, $oldValue, $newValue)
    {
        return call_user_func($this->callback, $sender, $propertyName, $oldValue, $newValue);
    }
}
