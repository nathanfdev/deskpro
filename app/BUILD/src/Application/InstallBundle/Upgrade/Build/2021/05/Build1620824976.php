<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1620824976 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', <<<'SQL'
UPDATE `report_widget` as `rw`
SET `rw`.`query` = 'SELECT
                SUM(IF(chat_conversations.rating_overall > 1,1,0)) AS Postive,
                SUM(IF(chat_conversations.rating_overall <= 1,1,0)) AS Negative
            FROM chat_conversations
            WHERE chat_conversations.rating_overall <> NULL
              AND chat_conversations.agent <> NULL
              AND chat_conversations.date_created = ${date}
            GROUP BY chat_conversations.agent'
WHERE `rw`.`unique_key` = 'chats-feedback-grouped-by-agent-x-date'
SQL
        );
    }

    public function run()
    {
    }
}
