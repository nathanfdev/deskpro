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

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;

class BackupTroublesStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Backup Troubleshooters';
	}

	public function run($page = 1)
	{
		$sub_start_time = microtime(true);
		$this->logMessage("Saving troubleshooter data");

		$troubles = $this->getOldDb()->fetchAll("SELECT * FROM trouble");

		foreach ($troubles as $t) {
			$this->getDb()->beginTransaction();

			try {
				$this->processTrouble($t);
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}
		}

		$sub_end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));
	}

	public function processTrouble($trouble)
	{
		$trouble['_comments']     = $this->getOldDb()->fetchAll("SELECT * FROM trouble_comments WHERE troubleid = ?", array($trouble['id']));
		$trouble['_permissions']  = $this->getOldDb()->fetchAll("SELECT * FROM trouble_permissions WHERE troubleid = ?", array($trouble['id']));
		$trouble['_questions']    = $this->getOldDb()->fetchAll("SELECT * FROM trouble_questions WHERE troubleid = ?", array($trouble['id']));
		$trouble['_rating']       = $this->getOldDb()->fetchAll("SELECT * FROM trouble_rating WHERE troubleid = ?", array($trouble['id']));

		$this->getDb()->replace('import_datastore', array('typename' => 'trouble.' . $trouble['id'], 'data' => serialize($trouble)));
	}
}
