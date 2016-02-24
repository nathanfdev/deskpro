<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Widget;

use Application\DeskPRO\NewSettings\SettingsResolver;

/**
 * Class WidgetSettings.
 */
class WidgetSettings
{
    const REQUIRE_LOGIN     = 'portal.chat.require_login';
    const EMAIL_VALIDATION  = 'portal.chat.email_validation';
    const ENABLED_ON_PORTAL = 'portal.widget.enabled';

    /**
     * @var SettingsResolver
     */
    protected $settings_resolver;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settings_resolver
     */
    public function __construct(SettingsResolver $settings_resolver)
    {
        $this->settings_resolver = $settings_resolver;
    }

    /**
     * @return bool
     */
    public function isPortalEmailValidation()
    {
        return (bool) $this->getGlobalSettings()->get(self::EMAIL_VALIDATION);
    }

    /**
     * @return bool
     */
    public function isPortalRequireLogin()
    {
        return (bool) $this->getGlobalSettings()->get(self::REQUIRE_LOGIN);
    }

    /**
     * @return bool
     */
    public function isEnabledOnPortal()
    {
        return (bool) $this->getGlobalSettings()->get(self::ENABLED_ON_PORTAL);
    }

    /**
     * @return array
     */
    public function getDefaultBrandSettings()
    {
        return [
            'widget' => [
                'type'                  => 'column',
                'position'              => 'right',
                'agent_polling_timeout' => 10,
            ],
            'button' => [
                'size'   => 'medium',
                'name'   => 'Help',
                'colors' => [
                    'background' => '#62ad8c',
                    'text'       => '#ffffff',
                    'border'     => '#4e9576',
                ],
            ],
            'chat' => [
                'enabled'           => true,
                'request_user_info' => true,
                'proactive'         => true,
                'popup'             => [
                    'title'      => 'DeskPRO Customer Support',
                    'message'    => 'Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.',
                    'reply_type' => 'buttons',
                ],
                'begin_mode'      => 'form',
                'waiting_timeout' => 30,
            ],
        ];
    }

    /**
     * @return \Application\DeskPRO\NewSettings\SettingsBag
     */
    protected function getGlobalSettings()
    {
        return $this->settings_resolver->getGlobalSettings();
    }
}
