<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1495206447 extends AbstractBuild implements OnlineBuildInterface
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
