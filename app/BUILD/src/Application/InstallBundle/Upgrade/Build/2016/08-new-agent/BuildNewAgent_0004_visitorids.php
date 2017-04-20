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

class BuildNewAgent_0004_visitorids extends AbstractBuild
{
    public function run()
    {
        $this->out('Modifying visitor_id columns');

        $sh = $this->getSchemaHelper();

        $tables = [
            'article_comments',
            'chat_blocks',
            'chat_conversations',
            'download_comments',
            'feedback_comments',
            'news_comments',
            'ratings',
            'searchlog',
            'sessions',
        ];

        foreach ($tables as $t) {
            $instructions = [];
            if ($fk = $sh->findForeignKey($t, 'visitor_id', 'visitors', 'id')) {
                $instructions[] = 'DROP FOREIGN KEY '.$fk->getName();
            }
            if ($idx = $sh->findIndex($t, 'visitor_id')) {
                $instructions[] = 'DROP INDEX '.$idx->getName();
            }

            $instructions[] = 'DROP visitor_id';

            // Using slow alter here mainly for searchlog and chat_conversations, which might be large
            $this->execSlowAlterTable($t, implode(', ', $instructions));

            // re-add visitor_id as free VARCHAR
            // needs to be a separate query because otherwise may fail on MySQL 5.6 due to Bug#14105491
            // https://dev.mysql.com/doc/relnotes/mysql/5.6/en/news-5-6-11.html
            $this->execSlowAlterTable($t, 'ADD visitor_id VARCHAR(120) NULL DEFAULT NULL');
        }
    }
}

//[[build:1460678404]]
