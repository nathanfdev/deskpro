<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

class Build1493393091 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->out('Changing portal.chat.enabled => core.apps_chat');
        $connection = $this->getDbConnection('default');

        $hasSetting = $connection->fetchAll('SELECT * FROM `settings_brand` WHERE `name` = "core.apps_chat"');
        if (!empty($hasSetting)) {
            return;
        }

        //lets find already defined portal.chat.enabled
        $globalPortalChatEnabled = $connection->fetchAll('SELECT * FROM `settings` WHERE `name` = "portal.chat.enabled"');
        if (empty($globalPortalChatEnabled)) {
            return;
        }

        $globalValue = array_pop($globalPortalChatEnabled);

        // update existing portal.chat.enabled to core.apps_chat
        $this->execDbQuery(
            'default',
            "UPDATE `settings_brand` SET `name` = 'core.apps_chat' WHERE `name` = 'portal.chat.enabled'"
        );
        $brands    = $connection->fetchAllCol('SELECT `id` FROM `brands`');
        $statement = <<<'SQL'
INSERT IGNORE 
  INTO `settings_brand` (`brand_id`, `name`, `value`)
  VALUES (:brand_id, 'core.apps_chat', :value)
SQL;

        $preparedStatement = $connection->prepare($statement);
        foreach ($brands as $brand) {
            $preparedStatement->execute([
                'brand_id' => $brand,
                'value'    => $globalValue ? $globalValue['value'] : 0,
            ]);
        }

        $this->execDbQuery('default', 'DELETE FROM `settings` WHERE `name` = "core.apps_chat"');
        $this->execDbQuery('default', 'DELETE FROM `settings` WHERE `name` = "portal.chat.enabled"');
    }
}
