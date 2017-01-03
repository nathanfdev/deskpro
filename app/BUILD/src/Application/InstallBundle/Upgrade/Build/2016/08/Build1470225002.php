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
