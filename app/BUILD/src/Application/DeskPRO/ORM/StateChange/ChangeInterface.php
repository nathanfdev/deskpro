<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

interface ChangeInterface
{
    public function getField();
    public function getOld();
    public function getNew();
    public function isSame();
    public function isCollection();
    public function isEntity();
}
