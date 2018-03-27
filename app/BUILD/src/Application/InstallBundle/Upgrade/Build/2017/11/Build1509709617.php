<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1509709617 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', '
            CREATE TABLE IF NOT EXISTS `content_subscriptions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `person_id` int(11) DEFAULT NULL,
                `article_id` int(11) DEFAULT NULL,
                `download_id` int(11) DEFAULT NULL,
                `feedback_id` int(11) DEFAULT NULL,
                `news_id` int(11) DEFAULT NULL,
                `use_email` tinyint(1) NOT NULL,
                `last_dismiss_date` datetime NOT NULL,
                `last_email_date` datetime NOT NULL,
                `updated_date` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `IDX_5FADAC10217BBB47` (`person_id`),
                KEY `IDX_5FADAC107294869C` (`article_id`),
                KEY `IDX_5FADAC10C667AEAB` (`download_id`),
                KEY `IDX_5FADAC10D249A887` (`feedback_id`),
                KEY `IDX_5FADAC10B5A459A0` (`news_id`),
                CONSTRAINT `FK_5FADAC10217BBB47` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_5FADAC107294869C` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_5FADAC10B5A459A0` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_5FADAC10C667AEAB` FOREIGN KEY (`download_id`) REFERENCES `downloads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_5FADAC10D249A887` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
