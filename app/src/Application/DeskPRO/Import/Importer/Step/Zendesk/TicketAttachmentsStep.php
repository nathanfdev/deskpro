<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Zendesk;

use Orb\Data\ContentTypes;

class TicketAttachmentsStep extends AbstractZendeskStep
{
	const PERPAGE = 250;

	public static function getTitle()
	{
		return 'Download Blobs';
	}

	public function countPages()
	{
		$count = $this->db->fetchColumn("
			SELECT COUNT(*) FROM import_datastore
			WHERE typename LIKE 'attach.person_picture.%'
		");

		if (!$count) {
			return 1;
		}

		return ceil($count / self::PERPAGE);
	}

	public function run($page = 1)
	{
		$perpage = self::PERPAGE;
		$start = ($page - 1) * $perpage;
		$batch = $this->db->fetchAll("
			SELECT * FROM import_datastore
			WHERE typename LIKE 'attach.person_picture.%'
			ORDER BY typename ASC
			LIMIT $start, $perpage
		");

		$this->getDb()->beginTransaction();
		try {
			foreach ($batch as $n) {
				$this->processBlob($n);
			}
			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}
	}

	/**
	 * @param array $blob_info
	 */
	protected function processBlob($blob_info)
	{
		$tmpfile = tempnam(sys_get_temp_dir(), 'dp');

		if (!copy($blob_info['url'], $tmpfile)) {
			$this->logMessage("Failed copy blob: " . print_r($blob_info,1));
			return;
		}

		$dim_w = $dim_h = 0;
		if (in_array($blob_info['content_type'], ContentTypes::getImageContentTypes())) {
			$imageinfo = @getimagesize($tmpfile);
			if ($imageinfo) {
				$dim_w = $imageinfo[0];
				$dim_h = $imageinfo[1];
			}
		}

		$desc = $this->getContainer()->getSystemService('filestorage')->createRandomPath();
		$desc->write($tmpfile, array(
			'content_type' => $blob_info['content_type'],
			'filename'     => $blob_info['filename'],
		));
		$new_blob_id = $desc->getPath();

		$this->db->update('blobs', array(
			'filename' => $blob_info['filename'],
			'filesize' => filesize($tmpfile),
			'dim_w' => $dim_w,
			'dim_h' => $dim_h,
			'date_created' => date('Y-m-d H:i:s'),
		), array('id' => $new_blob_id));

		$this->db->insert('tickets_attachments', array(
			'ticket_id'     => $blob_info['ticket_id'],
			'person_id'     => $blob_info['person_id'],
			'message_id'    => $blob_info['message_id'],
			'blob_id'       => $new_blob_id,
			'is_agent_note' => $blob_info['is_agent_note']
		));

		@unlink($tmpfile);
	}
}
