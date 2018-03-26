<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * A misc resource for doing things like testing if the system is up, or fetching
 * statistics etc.
 *
 * @ApiModes("all")
 */
class DeskproController extends AbstractController
{
    /**
     * Simple action to return the current server time.
     */
    public function timeAction()
    {
        return $this->createApiResponse([
            'timestamp' => time(),
            'fulldate'  => date('r'),
        ]);
    }

    /**
     * Gets the value of a setting.
     *
     * @param string $setting_name
     */
    public function settingAction($setting_name)
    {
        $value = $this->settings[$setting_name];

        if ($value === null) {
            return $this->createApiErrorResponse('setting_not_found', 'No setting was found with that name', 404);
        }

        return $this->createApiResponse(['setting_value' => $value]);
    }

    /**
     * Sets a new value for a setting.
     *
     * @param string $setting_name
     */
    public function postSettingAction($setting_name)
    {
        $current_value = $this->settings[$setting_name];

        if ($current_value === null) {
            return $this->createApiErrorResponse('setting_not_found', 'No setting was found with that name', 404);
        }

        $this->settings->setSetting($setting_name, isset($_POST['value']) ? $_POST['value'] : '');

        return $this->createApiResponse(['success' => 1]);
    }
}
