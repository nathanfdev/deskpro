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

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0025_comment_counts extends AbstractBuild
{
    public function run()
    {
        $this->execDbQueryQuiet('default',
            'UPDATE `articles` a
              LEFT JOIN (
                SELECT ac.`article_id`,
                  COUNT(ac.`id`) num_comments
                FROM `article_comments` ac
                WHERE ac.`status` = \'visible\'
                GROUP BY ac.`article_id`
              ) calc
              ON calc.`article_id` = a.`id`
              SET a.`num_comments` = IFNULL(calc.`num_comments`, 0);'
        );
        $this->execDbQueryQuiet('default',
            'UPDATE `downloads` d
              LEFT JOIN (
                SELECT dc.`download_id`,
                  COUNT(dc.`id`) num_comments
                FROM `download_comments` dc
                WHERE dc.`status` = \'visible\'
                GROUP BY dc.`download_id`
              ) calc
              ON calc.`download_id` = d.`id`
              SET d.`num_comments` = IFNULL(calc.`num_comments`, 0);'
        );
        $this->execDbQueryQuiet('default',
            'UPDATE `feedback` f
              LEFT JOIN (
                SELECT fc.`feedback_id`,
                  COUNT(fc.`id`) num_comments
                FROM `feedback_comments` fc
                WHERE fc.`status` = \'visible\'
                GROUP BY fc.`feedback_id`
              ) calc
              ON calc.`feedback_id` = f.`id`
              SET f.`num_comments` = IFNULL(calc.`num_comments`, 0);'
        );
        $this->execDbQueryQuiet('default',
            'UPDATE `news` n
              LEFT JOIN (
                SELECT nc.`news_id`,
                  COUNT(nc.`id`) num_comments
                FROM `news_comments` nc
                WHERE nc.`status` = \'visible\'
                GROUP BY nc.`news_id`
              ) calc
              ON calc.`news_id` = n.`id`
              SET n.`num_comments` = IFNULL(calc.`num_comments`, 0);'
        );
    }
}
