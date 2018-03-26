<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

use Orb\Util\Strings;

class BuildNewAgent_0002_publishslugs extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $cat_tables = [
            'article_categories',
            'download_categories',
            'feedback_categories',
            'news_categories',
        ];

        $content_tables = [
            'articles',
            'news',
            'downloads',
            'feedback',
        ];

        //###############################################################################################################
        // Cat slugs
        //###############################################################################################################

        $this->out('Add slugs to categories');

        $this->execMutateSql('ALTER TABLE article_categories ADD slug VARCHAR(255) NOT NULL');
        $this->execMutateSql('ALTER TABLE download_categories ADD slug VARCHAR(255) NOT NULL');
        $this->execMutateSql('ALTER TABLE feedback_categories ADD slug VARCHAR(255) NOT NULL');
        $this->execMutateSql('ALTER TABLE news_categories ADD slug VARCHAR(255) NOT NULL');

        foreach ($cat_tables as $cat_table) {
            $titles   = $db->fetchAllKeyValue("SELECT id, title FROM $cat_table ORDER BY id DESC");
            $dupe_map = [];

            foreach ($titles as $id => $t) {
                $t = Strings::slugifyTitle($t);
                if (isset($dupe_map[$t])) {
                    $t .= '-'.$id;
                    if (isset($dupe_map[$t])) {
                        $t .= '-'.mt_rand(1000, 9999);
                    }
                }
                $dupe_map[$t] = true;
                $db->update($cat_table, ['slug' => $t], ['id' => $id]);
            }
        }

        //###############################################################################################################
        // Article slugs
        //###############################################################################################################

        $this->out('Verify unique slugs on content');

        foreach ($content_tables as $content_table) {
            $titles   = $db->fetchAllKeyValue("SELECT id, slug FROM $content_table ORDER BY id DESC");
            $dupe_map = [];

            foreach ($titles as $id => $t) {
                $origT = $t;

                $t = Strings::slugifyTitle($t);
                $t = substr($t, 0, 90);
                $t = trim($t, '-');

                if (empty($t)) {
                    $t = $content_table.'-'.$id;
                }

                if (isset($dupe_map[$t])) {
                    $t .= '-'.$id;
                    if (isset($dupe_map[$t])) {
                        $t .= '-'.mt_rand(1000, 9999);
                    }
                }

                if ($origT !== $t) {
                    $db->update($content_table, ['slug' => $t], ['id' => $id]);
                }

                $dupe_map[$t] = true;
            }
        }

        //###############################################################################################################
        // Add unique indexes
        //###############################################################################################################

        $this->out('Add unique indexes on slugs');

        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_BFDD3168989D9B62 ON articles (slug)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_62A97E9989D9B62 ON article_categories (slug)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_4B73A4B5989D9B62 ON downloads (slug)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_3317F15989D9B62 ON download_categories (slug)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_D2294458989D9B62 ON feedback (slug)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_66FE6832989D9B62 ON feedback_categories (slug)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_1DD39950989D9B62 ON news (slug)');
        $this->execMutateSql('CREATE UNIQUE INDEX UNIQ_D68C9111989D9B62 ON news_categories (slug)');
    }
}

//[[build:1460678402]]
