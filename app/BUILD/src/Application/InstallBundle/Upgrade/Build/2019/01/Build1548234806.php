<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1548234806 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', sprintf('DELETE FROM `articles_slug_history` WHERE `slug` REGEXP \'^[0-9]+$\''));
        $this->execDbQuery('default', sprintf('DELETE FROM `feedback_slug_history` WHERE `slug` REGEXP \'^[0-9]+$\''));
        $this->execDbQuery('default', sprintf('DELETE FROM `downloads_slug_history` WHERE `slug` REGEXP \'^[0-9]+$\''));
        $this->execDbQuery('default', sprintf('DELETE FROM `news_slug_history` WHERE `slug` REGEXP \'^[0-9]+$\''));
        $this->execDbQuery('default', sprintf('DELETE FROM `topic_slug_history` WHERE `slug` REGEXP \'^[0-9]+$\''));
        $this->renameCurrentSlugs('articles', 'articles_slug_history', 'article-');
        $this->renameCurrentSlugs('feedback', 'feedback_slug_history', 'feedback-');
        $this->renameCurrentSlugs('downloads', 'downloads_slug_history', 'download-');
        $this->renameCurrentSlugs('news', 'news_slug_history', 'news-');
        $this->renameCurrentSlugs('topics', 'topic_slug_history', 'topic-');
    }

    private function renameCurrentSlugs($table, $historyTable, $prefix)
    {
        $db = $this->getDbConnection('default');

        $slugsToFix    = $db->fetchAll('SELECT id, slug FROM '.$table.' WHERE `slug` REGEXP \'^[0-9]+$\'');
        $existingSlugs = $db->fetchAllCol('SELECT slug FROM '.$table);
        $historySlugs  = $db->fetchAllCol('SELECT slug FROM '.$historyTable);
        $existingSlugs = array_merge($existingSlugs, $historySlugs);
        foreach ($slugsToFix as $slug) {
            $i = 1;
            while (in_array($prefix.$i, $existingSlugs)) {
                ++$i;
            }
            $this->execDbQuery('default', sprintf('UPDATE `%s` SET `slug` = \'%s\' WHERE id = %d', $table, $prefix.$i, $slug['id']));
            $existingSlugs[] = $prefix.$i;
        }
    }
}
