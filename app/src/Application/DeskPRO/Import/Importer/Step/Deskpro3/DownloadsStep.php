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

use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadRevision;

class DownloadsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Downloads';
	}

	public function run($page = 1)
	{
		// If there arent any downloads, delete default download cats
		$default_check = $this->getDb()->fetchColumn("SELECT id FROM downloads LIMIT 1");
		if (!$default_check) {
			$this->getDb()->exec("DELETE FROM downloads");
			$this->getDb()->exec("DELETE FROM download_categories");
		}

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM files_cats");
		if ($count) {
			$this->logMessage(sprintf("Importing %d download categories", $count));

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();
			try {
				$this->processCategories();
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all categories. Took %.3f seconds.", $end_time-$start_time));
		}

		if (!$this->getDb()->fetchColumn("SELECT id FROM download_categories LIMIT 1")) {
			// We need a default category that "top" level downloads will go into
			$new_cat = new DownloadCategory();
			$new_cat->title = 'General';
			$new_cat->display_order = 0;;
			$this->getEm()->persist($new_cat);
			$this->getEm()->flush();
		}


		$download_ids = $this->getOldDb()->fetchAllCol("SELECT id FROM files ORDER BY id ASC");
		if ($download_ids) {
			$this->logMessage(sprintf("Importing %d downloads", count($download_ids)));

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();
			try {
				foreach ($download_ids as $did) {
					$this->processDownload($did);
				}
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all downloads. Took %.3f seconds.", $end_time-$start_time));
		}
	}


	/**
	 * Process all categories
	 */
	protected function processCategories()
	{
		$cats = $this->getOldDb()->fetchAll("SELECT * FROM files_cats ORDER BY id ASC");
		if (!$cats) {
			return;
		}

		foreach ($cats as $cat) {
			#------------------------------
			# Make sure we havent already done them
			#------------------------------

			$check_exist = $this->getMappedNewId('file_cat', $cat['id']);
			if ($check_exist) {
				$this->getLogger()->log("{$cat['id']} already mapped, skipping", 'DEBUG');
				continue;
			}

			#------------------------------
			# Create it
			#------------------------------

			$new_cat = new DownloadCategory();
			$new_cat->title = $cat['name'];
			$new_cat->display_order = $cat['displayorder'];

			$this->getEm()->persist($new_cat);
			$this->getEm()->flush();

			$this->saveMappedId('file_cat', $cat['id'], $new_cat->id);
		}
	}


	/**
	 * Process a download
	 */
	protected function processDownload($download_id)
	{
		$download = $this->getOldDb()->fetchAssoc("SELECT * FROM files WHERE id = ?", array($download_id));

		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('file', $download['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$download['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_category = $this->getEm()->find('DeskPRO:DownloadCategory', $this->getMappedNewId('file_cat', $download['category']));
		if (!$new_category) {
			// Used to allow "0"
			if ($download['category'] == 0) {
				$new_category = $this->getEm()->createQuery("SELECT c FROM DeskPRO:DownloadCategory c ORDER BY c.id ASC")->setMaxResults(1)->getOneOrNullResult();
			}

			if (!$new_category) {
				$this->logMessage("{$download['id']} has an invalid category {$download['category']}, skipping");
				return;
			}
		}

		$new_person = null;
		if ($download['techid']) {
			$new_person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $download['techid']));
		}
		if (!$new_person) {
			$new_person = $this->getEm()->getRepository('DeskPRO:Person')->findOneBy(array('can_admin' => true));
		}

		$new_blob = $this->getEm()->find('DeskPRO:Blob', $this->getMappedNewId('blob', $download['blobid']));
		if (!$new_category) {
			$this->logMessage("{$download['id']} has an invalid blob, skipping");
			return;
		}

		$new_download = new Download();
		$new_download->setStatusCode(Download::STATUS_PUBLISHED);
		$new_download->blob = $new_blob;
		$new_download->category = $new_category;
		$new_download->person = $new_person;
		$new_download->title = $download['filename'];
		$new_download->content = $download['filename'];
		$new_download->date_created = new \DateTime('@' . $download['timestamp']);
		$new_download->date_published = new \DateTime('@' . $download['timestamp']);

		$this->getEm()->persist($new_download);
		$this->getEm()->flush();

		$this->saveMappedId('file', $download['id'], $new_download->id);

		#------------------------------
		# Create the first revision
		#------------------------------

		$revision = new DownloadRevision();
		$revision->download = $new_download;
		$revision->blob = $new_download->blob;
		$revision->title = $new_download->title;
		$revision->content = $new_download->content;

		$this->getEm()->persist($revision);
		$this->getEm()->flush();
	}
}
