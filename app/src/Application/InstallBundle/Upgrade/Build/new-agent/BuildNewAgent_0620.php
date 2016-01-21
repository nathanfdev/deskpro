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

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0620 extends AbstractBuild
{
    public function run()
    {
        $this->execMutateSql("CREATE TABLE hit_record (id INT AUTO_INCREMENT NOT NULL, visitor_id VARCHAR(120) DEFAULT NULL, ip_address VARCHAR(45) NOT NULL, page_type VARCHAR(255) NOT NULL, page_id VARCHAR(255) NOT NULL, url VARCHAR(1000) NOT NULL, referrer VARCHAR(1000) NOT NULL, user_agent VARCHAR(255) NOT NULL, geo_country VARCHAR(8) NOT NULL, meta LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', date_created DATETIME NOT NULL, INDEX visitor_id_idx (visitor_id), INDEX page_type_idx (page_type, page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }
}

//[[build:1456790462]]

