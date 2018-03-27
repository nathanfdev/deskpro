<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\TwitterSetup\TwitterSetup;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class TwitterSetupController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // twitter-setup
    //###################################################################################################################

    public function twitterSetupAction()
    {
        $twitter_setup = new TwitterSetup($this->settings);

        return $this->createApiResponse([
            'twitter_setup' => $twitter_setup->toArray(),
        ]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction()
    {
        $twitter_setup = new TwitterSetup($this->settings);
        $twitter_setup->setArray($this->in->getCleanValueArray('twitter_setup'));
        $twitter_setup->saveTwitterSetup();

        return $this->createSuccessResponse();
    }
}
