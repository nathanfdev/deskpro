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

class Build1415210938 extends AbstractBuild
{
	public function run()
	{
		$this->out("update slugs on content items");

		$conn = $this->container->getDb();

		$entity_tables = array(
			'articles',
			'downloads',
			'feedback',
			'news'
		);

		$conn->beginTransaction();

		try {

			foreach ($entity_tables as $table_name) {
				$this->ensureNoDuplicateSlug($table_name, $conn);
			}

			$this->execMutateSql("CREATE UNIQUE INDEX UNIQ_BFDD3168989D9B62 ON articles (slug)");
			$this->execMutateSql("CREATE UNIQUE INDEX UNIQ_4B73A4B5989D9B62 ON downloads (slug)");
			$this->execMutateSql("CREATE UNIQUE INDEX UNIQ_D2294458989D9B62 ON feedback (slug)");
			$this->execMutateSql("CREATE UNIQUE INDEX UNIQ_1DD39950989D9B62 ON news (slug)");

			$conn->commit();
		} catch (\Exception $e) {
			$conn->rollback();
		}
	}


	protected function ensureNoDuplicateSlug($table_name, Connection $conn)
	{
		$content_rows = $conn->fetchAll(
			"
			SELECT id,slug
			FROM $table_name
			WHERE slug IN
			(
				SELECT slug
			    FROM $table_name
			    GROUP BY slug
			    HAVING count(slug) > 1
			)
			"
		);

		$existing_slugs = array();
		foreach ($content_rows as $content_row) {
			// if we have this slug in the table already, we need to de-dupe and prepend the id
			$slug = $content_row['slug'];
			if (in_array($slug, $existing_slugs)) {
				$slug = $content_row['id'] . '-' . $slug;
			}
			$existing_slugs[] = $slug;
			$conn->update($table_name, array('slug' => $slug), array('id' => $content_row['id']));
		}
	}


}