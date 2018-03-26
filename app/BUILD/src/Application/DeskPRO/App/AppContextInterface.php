<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\Entity\AppInstance;

interface AppContextInterface
{
    /**
     * Sets the app instance.
     *
     * @param AppInstance $app
     */
    public function setApp(AppInstance $app);
}
