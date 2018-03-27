<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

/**
 * Change classes that implmenet this interface are indicating they are just using state change object
 * to track info and it should not change the 'version' of the underlying object.
 */
interface NonStateTrackingInterface
{
}
