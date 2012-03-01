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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketParticipant;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;

class UserChatsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Tickets';
	}

	public function countPages()
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM chat_chat");
		if (!$count) {
			return 1;
		}

		return ceil($count / 1000);
	}

	public function run($page = 1)
	{
		$sub_start_time = microtime(true);
		$batch = $this->getIdsBatch($page - 1);
		$this->logMessage("-- Processing batch {$page}");

		try {
			$this->getDb()->beginTransaction();
			foreach ($batch as $cid) {
				$this->processChat($cid);
			}
			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$sub_end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));
	}

	public function processChat($chat_id)
	{
		#------------------------------
		# Make sure we havent already done it
		#------------------------------

		$check_exist = $this->getMappedNewId('chat', $chat_id);
		if ($check_exist) {
			$this->getLogger()->log("{$chat_id} already mapped, skipping", 'DEBUG');
			return;
		}

		$chat_info = $this->getOldDb()->fetchAssoc("SELECT * FROM chat_chat WHERE id = $chat_id");

		#------------------------------
		# Make the chat
		#------------------------------

		$convo = new ChatConversation();
		$convo->status = 'ended';

		// Department
		$chat_dep = null;
		if ($chat_info['depid']) {
			$dep_id = $this->getMappedNewId('chat_dep', $chat_info['depid']);
			if ($dep_id) {
				$convo->department = $this->getEm()->find('DeskPRO:Department', $dep_id);
			}
		}

		// User
		if ($chat_info['userid']) {
			$person_id = $this->getMappedNewId('user', $chat_info['userd']);
			if ($person_id) {
				$convo->person = $this->getEm()->find('DeskPRO:Person', $person_id);
			}
		}

		// Agent
		if ($chat_info['userid']) {
			$person_id = $this->getMappedNewId('user', $chat_info['userd']);
			if ($person_id) {
				$convo->agent = $this->getEm()->find('DeskPRO:Person', $person_id);
			}
		}

		$convo->subject = $chat_info['subject'];

		if ($chat_info['rating'] >= 1) {
			$convo->rating_response_time = $chat_info['rating'];
			$convo->rating_overall = $chat_info['rating'];
		}

		if ($chat_info['feedback']) {
			$convo->rating_comment = $chat_info['feedback'];
		}

		if ($chat_info['useremail']) {
			$convo->person_email = $chat_info['useremail'];
		}
		if ($chat_info['userdisplayname']) {
			$convo->person_name = $chat_info['userdisplayname'];
		}

		$convo->date_created = new \DateTime('@' . (int)$chat_info['timestamp_start']);
		if ($chat_info['timestamp_assigned']) {
			$convo->date_assigned = new \DateTime('@' . (int)$chat_info['timestamp_assigned']);
		}
		$convo->date_ended = new \DateTime('@' . (int)($chat_info['timestamp_assigned'] ?: $chat_info['timestamp_start']));

		$this->getEm()->persist($convo);
		$this->getEm()->flushs();

		$this->saveMappedId('chat', $chat_info['id'], $convo->id);

		#------------------------------
		# Attachments
		#------------------------------

		$chat_attachments = $this->getOldDb()->fetchAll("SELECT * FROM chat_attachment WHERE ticketid = ?", array($ticket_info['id']));

		$chat_attach_info = array();

		foreach ($chat_attachments as $attach_info) {
			$blob_id = $this->getMappedNewId('blob', $attach_info['blobid']);
			if (!$blob_id) {
				continue;
			}

			$chat_attach_info[$id] = array('blob_id' => $blob_id, 'filename' => $attach_info['filename'], 'filesize' => 0);
		}


		#------------------------------
		# Add messages
		#------------------------------

		$all_message_info = $this->getOldDb()->fetchAll("
			SELECT * FROM chat_message
			WHERE chatid = ?
			ORDER BY id ASC
		", array($chat_info['id']));

		$first_agent_date = null;
		foreach ($all_message_info as $message_info) {
			$add_end = false;
			$message = new ChatMessage();
			$message->date_created = new \DateTime('@' . (int)$message_info['timestamp_sent']);
			if ($message_info['visibility'] == 'tech') {
				$message->is_user_hidden = true;
			}

			// Tech message
			if ($message_info['authortype'] == 'tech') {
				$first_agent_date = $message->date_created;
				$agent = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $message_info['authorid']));
				if (!$agent) {
					continue;
				}
				$message->author = $agent;
				$message->content = $message_info['message'];

			// User message
			} elseif ($message_info['authortype'] == 'user') {
				$person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $message_info['authorid']));
				if (!$person) {
					continue;
				}
				$message->author = $person;
				$message->content = $message_info['message'];

			// System message
			} else {
				$message->is_sys = true;

				// assign:from:0:to:8
				if (preg_match('#^assign:from:([0-9]+):to:([0-9]+)$#', $chat_info['message'], $m)) {
					if ($m[1]) {
						$old_agent = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $m[1]));
						if (!$old_agent) {
							continue;
						}
					} else {
						$old_agent = null;
					}

					if ($m[2]) {
						$new_agent = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $m[2]));
						if (!$new_agent) {
							continue;
						}
					} else {
						$new = null;
					}

					if ($old_agent && !$new_agent) {
						$message->content = App::getTranslator()->phrase('user.chat.unassigned');
					} elseif (!$old_agent && $new_agent) {
						$message->content = App::getTranslator()->phrase('user.chat.assigned_to', array(
							'name' => $new_agent->getDisplayName()
						));
					} else {
						continue;
					}

				// attachment:attachmentId:1:fileName:Front Page.bmml:techId:1
				} elseif (preg_match('#^attachment:attachmentId:([0-9]+):fileName:(.*?):techId:(.*?)$#', $chat_info['message'], $m)) {

					$attach_id = $m[1];
					$filename = $m[2];
					$tech_id = $m[3];

					if (!isset($chat_attach_info[$attach_id])) {
						continue;
					}

					$new_agent = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $tech_id));
					if (!$new_agent) {
						continue;
					}

					$blob = $this->getEm()->find('DeskPRO:Blob', $chat_attach_info[$attach_id]);
					if (!$blob) {
						continue;
					}

					$message->author  = $new_agent;
					$message->content = '<a href="' . $blob->getDownloadUrl() . '">' . htmlspecialchars($filename) . '</a>';
					$message->is_html = true;

				// end:comment:xxxxx:tech:8
				} elseif (preg_match('#^end:comment:(.*?)$#', $chat_info['message'], $m)) {
					list ($end_comment, , $agent_id) = \Orb\Util\Strings::rexplode($m[1], 3);

					$agent = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $agent_id));
					if (!$agent) {
						continue;
					}

					$message->author = $agent;
					$message->content = str_replace('\\:', ':', $end_comment);

					$add_end = new ChatMessage();
					$add_end->date_created = new \DateTime('@' . (int)$message_info['timestamp_sent']);
					$add_end->is_sys = true;
					$add_end->content = App::getTranslator()->phrase('user.chat.ended');

				} elseif (preg_match('#^end:who:user$#', $chat_info) || preg_match('#^end:who:user:timeout:#', $chat_info)) {
					$message->content = App::getTranslator()->phrase('user.chat.ended');
				}
			}

			$this->getEm()->persist($message);

			if ($add_end) {
				$this->getEm()->persist($add_end);
			}

			$this->getEm()->flush();
		}

		if ($first_agent_date) {
			$convo->date_first_agent_message = $first_agent_date;
			$this->getEm()->persist($convo);
			$this->getEm()->flush();
		}
	}

	/**
	 * @param $page
	 * @return array
	 */
	protected function getIdsBatch($page)
	{
		$start = $page * 1000;
		$ids = $this->getOldDb()->fetchAllCol("SELECT id FROM chat_chat ORDER BY id ASC LIMIT $start, 1000");

		return $ids;
	}
}
