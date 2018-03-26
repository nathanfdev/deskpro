<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0010_brand extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        //------------------------------
        // Available themes
        //------------------------------

        $this->execMutateSql("
            INSERT INTO `theme_sets` (`id`, `theme_id`, `options`)
            VALUES (1, 'standard', '{}'), (2, 'sidebar', '{}'), (3, 'standard', '{}')
        ");

        //------------------------------
        // Create our brand
        //------------------------------

        $site_name = $this->readSetting('core.deskpro_name');

        $brand = [
            'id'                => 1,
            'name'              => $site_name,
            'theme_set_id'      => 1,
            'edit_theme_set_id' => 3,
        ];

        $db->insert('brands', $brand);

        // Make sure it's set to the default brand ID
        $brand_id = $db->lastInsertId();
        $this->saveSetting('portal.default_brand', $brand_id);
        $this->saveSetting('portal.widget.enabled', 1);
        $this->saveSetting('portal.chat.enabled', 1);
    }
}

//[[build:1460678406]]
