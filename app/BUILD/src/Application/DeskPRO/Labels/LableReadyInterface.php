<?php

/**
 * DeskPRO.
 *
 * @category ORM
 */

namespace Application\DeskPRO\Labels;

interface LableReadyInterface
{
    public function removeLabel($label);
    public function addLabel($label);
}
