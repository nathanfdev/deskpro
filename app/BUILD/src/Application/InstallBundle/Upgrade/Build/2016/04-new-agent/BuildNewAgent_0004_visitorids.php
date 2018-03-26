<?php

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
