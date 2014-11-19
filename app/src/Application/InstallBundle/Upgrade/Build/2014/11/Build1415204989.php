<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Doctrine\DBAL\Connection;
use Orb\Util\Strings;

class Build1415204989 extends AbstractBuild
{
    public function run()
    {
        $this->out("slugify content categories");

        /** var \Doctrine\DBAL\Connection $conn */
        $conn = $this->container->getDb();

        // wrap it all in a transaction
        $that = $this;
        $conn->transactional(function ($conn) use ($that) {
                $cat_tables = array(
                    'article_categories',
                    'download_categories',
                    'feedback_categories',
                    'news_categories'
                );

                foreach ($cat_tables as $cat_table) {
                    $that->addSlugCol($cat_table);
                    $that->generateAndUpdateSlugForCategory($cat_table, $conn);
                }

                $that->execMutateSql("CREATE UNIQUE INDEX UNIQ_62A97E9989D9B62 ON article_categories (slug)");
                $that->execMutateSql("CREATE UNIQUE INDEX UNIQ_3317F15989D9B62 ON download_categories (slug)");
                $that->execMutateSql("CREATE UNIQUE INDEX UNIQ_66FE6832989D9B62 ON feedback_categories (slug)");
                $that->execMutateSql("CREATE UNIQUE INDEX UNIQ_D68C9111989D9B62 ON news_categories (slug)");
            }
        );
    }


    public function generateAndUpdateSlugForCategory($cat_table, Connection $conn)
    {
        $cat_rows = $conn->fetchAll('SELECT id,title FROM ' . $cat_table);

        $existing_slugs = array();
        foreach($cat_rows as $cat) {
            $slug = Strings::slugifyTitle($cat['title']);
            // if we have this slug in the table already, we need to de-dupe and prepend the id
            if (in_array($slug, $existing_slugs)) {
                $slug = $cat['id'] . '-' . $slug;
            }
            $existing_slugs[] = $slug;
            $conn->update($cat_table, array('slug' => $slug), array('id' => $cat['id']));
        }
    }

    public function addSlugCol($cat_table)
    {
        $this->execMutateSql("ALTER TABLE $cat_table ADD slug VARCHAR(255) NOT NULL");
    }
}
