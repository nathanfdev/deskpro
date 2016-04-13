<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Component\Util\RandUtils;
use Orb\Util\Strings;

class BuildNewAgent_0900_post extends AbstractBuild
{
    public function run()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if (!$DP_ENV->getDatManager()->hasTxtFile('server_info_auth')) {
            $this->out('Init server info auth code');
            $DP_ENV->getDatManager()->writeTxtFile('server_info_auth', Strings::random(30, Strings::CHARS_ALPHANUM_IU));
        }

        $this->out('Enable chat widget by default on portal');
        $db = $this->container->get('database_connection');
        $db->delete('datastore', ['name' => 'widget.portal_brand_settings']);

        // This is a default serialized WidgetBrandSettings object to enable the widget
        // It's normally applied from the admin interface when the widget is enabled
        $widgetSettingsBlob = base64_decode(trim(file_get_contents(__DIR__.'/data/WidgetBrandSettings.dat')));

        $db->insert('datastore', [
            'name' => 'widget.portal_brand_settings',
            'data' => $widgetSettingsBlob,
            'auth' => RandUtils::randomStringFormat('%15An'),
        ]);

        $db->executeUpdate('
            INSERT INTO settings
                (name, value)
            VALUES
                (?, ?)
            ON DUPLICATE KEY UPDATE
                value = VALUES(value)
        ', [WidgetSettingsResolver::ENABLED_ON_PORTAL, '1']);
    }
}
