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
use Orb\Data\ContentTypes;

// DP3's blob table doesnt store any info about the type of file, filesize etc
// But the individual content tables (ie ticket_attachments) does.
// So we'll just go through them one by one and process blobs in each
// - This has the nifty sideeffect of us easily ignoring abandoned blobs and that makes everyone smile

// Note this is JUST about importing blobs.
// Actually re-connecting these content tables is up to the steps that import that type of content
// I.e., we import blobs from ticket attachments into our blobs table, but the tickets step will actually make the relevant
// ticket attachment records.
abstract class AbstractBlobsStep extends AbstractDeskpro3Step
{
	abstract public function getTable();

	public function countPages()
	{
		$table = $this->getTable();
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM $table");
		if (!$count) {
			return 1;
		}

		$pages = ceil($count / 500);
		return $pages;
	}

	public function run($page = 1)
	{
		$table = $this->getTable();

		$batch = $this->getIdsBatch($table, $page - 1);
		foreach ($batch as $rid) {
			$this->processBlob($table, $rid);
		}
	}

	protected function processBlob($table, $record_id)
	{
		$record = $this->getOldDb()->fetchAssoc("SELECT * FROM $table WHERE id = ?", array($record_id));
		$blob = $this->getOldDb()->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($record['blobid']));

		if (!$blob) {
			return;
		}

		$check = $this->getMappedNewId('blob', $blob['id']);
		if ($check) {
			return;
		}

		$filetype = ContentTypes::getContentTypeFromExtension($record['extension']);
		if (!$filetype) {
			$filetype = 'application/octet-stream';
		}

		$file = $this->getOldDb()->fetchAllCol("
			SELECT blobdata
			FROM blob_parts
			WHERE blobid = ?
			ORDER BY displayorder ASC
		", array($record['blobid']));
		$file = implode('', $file);

		$hash = sha1($file);

		$dim_w = $dim_h = 0;
		if (in_array($filetype, ContentTypes::getImageContentTypes())) {
			$tmpfname = @tempnam(sys_get_temp_dir(), "dpblob_");
			if ($tmpfname && @file_put_contents($tmpfname, $file)) {
				$imageinfo = @getimagesize($tmpfname);
				if ($imageinfo) {
					$dim_w = $imageinfo[0];
					$dim_h = $imageinfo[1];
				}
			}
			@unlink($tmpfname);
		}

		$this->getDb()->insert('blobs', array(
			'save_path' => $blob['filepath'],
			'filename' => $record['filename'],
			'filesize' => $record['filesize'],
			'content_type' => $filetype,
			'authcode' => Strings::random(20, Strings::CHARS_KEY),
			'blob_hash' => $hash,
			'dim_w' => $dim_w,
			'dim_h' => $dim_h,
			'date_created' => date('Y-m-d H:i:s', $record['timestamp']),
		));

		$new_blob_id = $this->getDb()->lastInsertId();

		$this->saveMappedId('blob', $blob['id'], $new_blob_id);

		// Insert the parts too
		$data_len = strlen($file);

		// /2 for worst-case scenario of every character needing escape, -200 for wiggle room fo rest of query
		$max_size = ($this->getDb()->getMaxPacketSize()/2)-200;
		$parts = ceil($data_len / $max_size);

		for ($i = 0; $i < $parts; $i++) {
			$this->getDb()->insert('blobs_storage', array(
				'blob_id' => $new_blob_id,
				'data' => substr($file, $i * $max_size, $max_size)
			));
		}
	}


	/**
	 * @param $page
	 * @return array
	 */
	protected function getIdsBatch($table, $page)
	{
		$start = $page * 500;
		$ids = $this->getOldDb()->fetchAllCol("SELECT id FROM $table ORDER BY id ASC LIMIT $start, 500");

		return $ids;
	}
}
