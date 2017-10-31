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

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1509038617 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->out('Creating report dashboards tables');
        $this->execDbQuery('default', '
CREATE TABLE `report_widget` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`parent_id` INT(11) NULL DEFAULT NULL,
	`unique_key` VARCHAR(50) NULL DEFAULT NULL,
	`title` VARCHAR(255) NOT NULL,
	`description` LONGTEXT NOT NULL,
	`query` LONGTEXT NOT NULL,
	`is_custom` TINYINT(1) NOT NULL,
	`labels` TINYTEXT NULL COMMENT \'(DC2Type:simple_array)\',
	`display_order` INT(11) NOT NULL,
	`display_types` LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
	`variables` LONGTEXT NULL COMMENT \'(DC2Type:json_array)\',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `unique_key_idx` (`unique_key`),
	INDEX `parent_id_idx` (`parent_id`),
	CONSTRAINT `FK_AC0ACB4F727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `report_widget` (`id`) ON DELETE SET NULL
)
COLLATE=\'utf8_general_ci\'
ENGINE=InnoDB
;');
        $this->execDbQuery('default', 'CREATE TABLE report_widget_favorite like report_builder_favorite;');
        $this->execDbQuery('default', "CREATE TABLE report_dashboard (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_default TINYINT(1) DEFAULT '0' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execDbQuery('default', 'CREATE TABLE report_dashboard_report (id INT AUTO_INCREMENT NOT NULL, dashboard_id INT NOT NULL, title VARCHAR(255) NOT NULL, sort_order INT(1) UNSIGNED NOT NULL, columns INT NOT NULL DEFAULT 10, INDEX IDX_6EE5C64BB9D04D2B (dashboard_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', "CREATE TABLE report_dashboard_widget (id INT AUTO_INCREMENT NOT NULL, widget_id INT NOT NULL, report_id INT NOT NULL, title VARCHAR(255) NOT NULL, position VARCHAR(5) NOT NULL, size VARCHAR(5) NOT NULL, hc_data VARCHAR(50) NULL DEFAULT NULL,	type VARCHAR(50) NULL DEFAULT NULL, variables TINYTEXT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_2F33AF1FFBE885E2 (widget_id), INDEX IDX_2F33AF1F4BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execDbQuery('default', 'CREATE TABLE report_dashboard_permission (id INT AUTO_INCREMENT NOT NULL, dashboard_id INT DEFAULT NULL, person_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, INDEX IDX_DED8DEFB9D04D2B (dashboard_id), INDEX IDX_DED8DEF217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'INSERT INTO report_widget_favorite (SELECT * FROM report_builder_favorite);');
        $this->execDbQuery('default', '
			ALTER TABLE `report_widget_favorite`
          	  DROP INDEX `IDX_CCD5CB1186DD4ADF`,
	          DROP INDEX `unique_key_idx`;
	          ALTER TABLE `report_widget_favorite` CHANGE COLUMN `report_builder_id` `report_widget_id` INT(11) NULL DEFAULT NULL AFTER `id`,
			    ADD CONSTRAINT `FK_CCD5CB1186DD4ADF` FOREIGN KEY (`report_widget_id`) REFERENCES `report_widget` (`id`) ON DELETE CASCADE,
			    ADD INDEX `unique_key_idx` (`report_widget_id`, `person_id`, `params`);
        ');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_report ADD CONSTRAINT FK_6EE5C64BB9D04D2B FOREIGN KEY (dashboard_id) REFERENCES report_dashboard (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_widget ADD CONSTRAINT FK_2F33AF1FFBE885E2 FOREIGN KEY (widget_id) REFERENCES report_widget (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_widget ADD CONSTRAINT FK_2F33AF1F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report_dashboard_report (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_permission ADD CONSTRAINT FK_DED8DEFB9D04D2B FOREIGN KEY (dashboard_id) REFERENCES report_dashboard (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_permission ADD CONSTRAINT FK_DED8DEF217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }
    public function run()
    {
        $sql = <<<'SQL'
SELECT id, query, variables, title, display_types FROM `report_widget` where is_custom = 1
SQL;
        $widgets = $this->getDbConnection()->fetchAll($sql);
        foreach ($widgets as $widget) {
            $vars        = [];
            $fetchedVars = isset($widget['variables']) ? json_decode($widget['variables'], true) : [];
            if ($fetchedVars) {
                foreach ($fetchedVars as $var) {
                    $vars[$var['name']] = $var;
                }
            }
            $query        = $widget['query'];
            $displayTypes = $widget['display_types'] ? explode(',', $widget['display_types']) : [];

            $query = preg_replace_callback(
                '/%(\d+):DATE_GROUP%/',
                function ($match) use (&$vars) {
                    $varName = 'date_'.$match[1];
                    $vars[$varName] = ['name' => $varName, 'type' => 'dates'];

                    return '${date_'.$match[1].'}';
                },
                $query
            );
            $title = $widget['title'];

            $query = preg_replace_callback('/%(\d+):FIELD_GROUP:([^:%]+)(:([^%]+))?%/',
                function ($match) use (&$vars) {
                    $type = $match[2];
                    $table = isset($match[4]) ? $match[4] : $type;

                    $varName = 'group_by_field_'.$match[1];

                    $vars[$varName] = [
                        'name'       => $varName,
                        'type'       => 'fields',
                        'field_type' => $type,
                        'table'      => $table,
                    ];

                    return '${'.$varName.'}';
                },
                $query);

            $query = preg_replace_callback('/%(\d+):STATUS_GROUP:([^:%]+)(:([^%]+))?%/',
                function ($match) use (&$vars) {
                    $type = $match[2];
                    $table = isset($match[4]) ? $match[4] : $type;

                    $varName = 'status_field_'.$match[1];

                    $vars[$varName] = [
                        'name'       => $varName,
                        'type'       => 'statuses',
                        'field_type' => $type,
                        'table'      => $table,
                    ];

                    return '${'.$varName.'}';
                },
                $query);

            $query = preg_replace_callback('/%(\d+):ORDER_GROUP:([^:%]+)(:([^%]+))?%/',
                function ($match) use (&$vars) {
                    $type = $match[2];
                    $table = isset($match[4]) ? $match[4] : $type;

                    $varName = 'order_field_'.$match[1];

                    $vars[$varName] = [
                        'name'       => $varName,
                        'type'       => 'orders',
                        'field_type' => $type,
                        'table'      => $table,
                    ];

                    return '${'.$varName.'}';
                },
                $query);

            $title = preg_replace_callback(
                '/<(\d+):date group, default: ([a-z_]+)?>/',
                function ($match) use (&$vars) {
                    $varName = 'date_'.$match[1];
                    if (isset($vars[$varName]) && isset($match[3])) {
                        $vars[$varName]['default'] = strval($match[3]);
                    }

                    return '<date>';
                },
                $title
            );

            $title = preg_replace_callback(
                '/<(\d+):(order|status|field) group:([a-z_]+)(, default:( )?([a-z_]+))?>/',
                function ($match) use (&$vars) {
                    switch ($match[2]) {
                        case 'status':
                            $varName = 'status_field_'.$match[1];
                            $return = '<'.$match[2].' status>';
                            break;
                        case 'order':
                            $varName = 'order_field_'.$match[1];
                            $return = '<'.$match[2].' field>';
                            break;
                        default:
                            $varName = 'group_by_field_'.$match[1];
                            $return = '<'.$match[3].' field>';
                            break;
                    }
                    if (isset($vars[$varName]) && isset($match[6])) {
                        $vars[$varName]['default'] = strval($match[6]);
                    }

                    return $return;
                },
                $title
            );

            $title = preg_replace_callback(
                '/ <chart:([a-z]+)>/',
                function ($match) use (&$displayTypes) {
                    $this->out(var_export($match, true));
                    switch ($match[1]) {
                        case 'bar':
                            $addType = 'simple_bars';
                            break;
                        case 'area':
                            $addType = 'simple_area';
                            break;
                        case 'pie':
                            $addType = 'pie';
                            break;
                        case 'line':
                            $addType = 'simple_lines';
                            break;
                        default:
                            $addType = 'table';
                            break;
                    }
                    $displayTypes[] = $addType;
                    $this->out(var_export($match, true));

                    return '';
                },
                $title
            );

            $vars         = json_encode(array_values($vars));
            $displayTypes = $displayTypes ? implode(',', array_unique($displayTypes)) : 'table,simple_bars,pie,simple_area,simple_lines';
            $update       = <<<UPDATE
UPDATE `report_widget` 
   SET `query` = "{$query}",
       `title`  = "{$title}",
       `display_types` = "{$displayTypes}",
       `variables` = '{$vars}'
 WHERE `id` = {$widget['id']}
UPDATE;

            $this->execDbQuery('default', $update);
        }
    }
}
