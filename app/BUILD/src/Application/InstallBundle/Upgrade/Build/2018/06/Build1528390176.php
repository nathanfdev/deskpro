<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1528390176 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS `saved_dashboard_widget`;');

        $this->execDbQuery('default', '
CREATE TABLE IF NOT EXISTS `saved_dashboard_widget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dashboard_widget_id` int(11) DEFAULT NULL,
  `saved_report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `position` varchar(5) NOT NULL,
  `size` varchar(5) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `variables` longtext COMMENT \'(DC2Type:json_array)\',
  `options` longtext,
  `data` longtext COMMENT \'(DC2Type:json_array)\',
  PRIMARY KEY (`id`),
  KEY `IDX_7EEC518DB31FDD11` (`dashboard_widget_id`),
  KEY `IDX_7EEC518D7E09ED3D` (`saved_report_id`),
  CONSTRAINT `FK_7EEC518D7E09ED3D` FOREIGN KEY (`saved_report_id`) REFERENCES `saved_dashboard_report` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7EEC518DB31FDD11` FOREIGN KEY (`dashboard_widget_id`) REFERENCES `report_dashboard_widget` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
