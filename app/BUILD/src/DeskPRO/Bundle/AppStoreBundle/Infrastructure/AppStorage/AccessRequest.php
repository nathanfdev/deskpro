<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppStorage;

interface AccessRequest
{
    /**
     * @return string
     */
    public function getAuthPersonId();

    /**
     * @return string
     */
    public function getAccessLevel();
}
