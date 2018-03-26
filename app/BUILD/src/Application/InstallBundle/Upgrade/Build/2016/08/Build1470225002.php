<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1470225002 extends AbstractBuild
{
    public function run()
    {
        $this->out('Cleanup empty slug');

        $connection = $this->getDbConnection('default');

        $contentTables = [
            'articles'            => 'article',
            'article_categories'  => 'article',
            'news'                => 'news',
            'news_categories'     => 'news',
            'feedback'            => 'feedback',
            'feedback_categories' => 'feedback',
            'downloads'           => 'download',
            'download_categories' => 'download',
        ];

        foreach ($contentTables  as $table => $slugFallback) {
            $sql = <<<SQL
SELECT * FROM `{$table}` WHERE `slug` = ""
SQL;
            // slug is unique so we need only the first
            $content = $connection->fetchAll($sql);
            if (count($content)) {
                $content = array_pop($content);

                $i    = 0;
                $slug = $slugFallback;
                while ($this->hasSlug($table, $slug)) {
                    ++$i;
                    $slug = sprintf('%s-%d', $slugFallback, $i);
                }
                $updateSQL = <<<SQL
    UPDATE `{$table}` SET `slug` = "{$slug}" WHERE `id` = {$content['id']}
SQL;
                $connection->executeUpdate($updateSQL);
            }
        }
    }

    private function hasSlug($table, $slug)
    {
        $connection = $this->getDbConnection('default');

        $sql = <<<SQL
SELECT `id` FROM `{$table}` WHERE `slug` = "{$slug}"
SQL;
        $content = $connection->fetchColumn($sql);

        return (bool) $content;
    }
}
