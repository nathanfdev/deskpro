<?php

namespace Application\DeskPRO\People;

interface PermissionsSetInterface
{
    /**
     * Get permission value by it name (ex.: tickets.reopen_resolved)
     * Usually this will be true/false values
     * But some permissions can have other than bool value (ex.: tickets.reopen_resolved_timelimit)
     * Returns FALSE if permission doesn't exist.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getByName($name);
}
