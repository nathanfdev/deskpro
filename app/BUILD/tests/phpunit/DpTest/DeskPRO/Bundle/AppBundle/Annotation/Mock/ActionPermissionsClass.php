<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\Mock;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use DeskPRO\Bundle\AppBundle\Annotation\Limits\Annotation\ApiDisableLimits;

/**
 * Class AbstractActionPermissionsClass.
 *
 * @ApiModes("standard")
 * @ApiTags("class.mock")
 */
class ActionPermissionsClass
{
    /**
     * @ApiDisableLimits()
     */
    public function inheritAction()
    {
    }

    /**
     * @ApiModes("all")
     */
    public function overrideModesAction()
    {
    }

    /**
     * @ApiTags("class.overridden")
     */
    public function overrideTagsAction()
    {
    }

    /**
     * @ApiModes({"token", "key"})
     * @ApiTags({"class.overridden", "class.overridden2"})
     */
    public function overrideBothAction()
    {
    }
}
