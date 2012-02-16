<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Orb\Util\Strings;
use Application\DeskPRO\App;
use Orb\Data\ContentTypes;

abstract class AbstractSearchIndexStep extends AbstractDeskpro3Step
{
	const PER_PAGE = 50;

	abstract public function getContentType();
	abstract public function getTable();
	abstract public function getEntity();

	public function countPages()
	{
		$count = $this->getDb()->fetchColumn("SELECT COUNT(*) FROM {$this->getTable()}");
		if (!$count) {
			return 1;
		}

		$pages = ceil($count / self::PER_PAGE);
		return $pages;
	}

	public function run($page = 1)
	{
		if ($page == 1) {
			App::getContainer()->getSearchAdapter()->deleteContentTypeFromIndex($this->getContentType());
		}

		$start = ($page-1) * self::PER_PAGE;
		$limit = self::PER_PAGE;
		$ids = $this->getContainer()->getDb()->fetchAllCol("
			SELECT id
			FROM {$this->getTable()}
			ORDER BY id ASC
			LIMIT $start, $limit
		");

		$batch = $this->getContainer()->getEm()->getRepository($this->getEntity())->getByIds($ids);
		if ($batch) {
			$this->getContainer()->getSearchAdapter()->updateObjectsInIndex($batch);
		}
	}
}
