<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\Person;

class NewReply
{
	public $message;

	/**
	 * @var \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public $new_upload = null;

	public $attach_ids = array();
	public $attach_ids_authed = false;

	protected $ticket;
	protected $person;

	protected $ticket_message;

	public function __construct(Ticket $ticket, Person $person)
	{
		$this->ticket = $ticket;
		$this->person = $person;
	}

	public function save()
	{
		$ticket_message = new TicketMessage();
		$ticket_message['message'] = htmlspecialchars($this->message);
		$ticket_message->ticket = $this->ticket;
		$ticket_message->person = $this->person;

		$attach = false;
		if ($this->new_upload) {
			$desc = App::getApi('filestorage')->createRandomPath();

			$desc->write(file_get_contents($this->new_upload->getRealPath()), array(
				'content_type' => $this->new_upload->getMimeType(),
				'filename' => $this->new_upload->getClientOriginalName()
			));

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new \Application\DeskPRO\Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$ticket_message->addAttachment($attach);
		}
		// Existing (pre-uploaded temp) attachments
		if ($this->attach_ids) {
			foreach ($this->attach_ids as $blob_id) {
				if ($this->attach_ids_authed) {
					$blob = App::getEntityRepository('DeskPRO:Blob')->getByAuthId($blob_id);
				} else {
					$blob = App::findEntity('DeskPRO:Blob', $blob_id);
				}
				if ($blob) {
					$attach = new \Application\DeskPRO\Entity\TicketAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $this->person;

					$ticket_message->addAttachment($attach);

					$blob->is_temp = false;
					App::getOrm()->persist($attach);
					App::getOrm()->persist($blob);
				}
			}
		}

		$this->ticket->addMessage($ticket_message);

		if ($dupe_message = App::getEntityRepository('DeskPRO:TicketMessage')->checkDupeMessage($ticket_message, $this->ticket)) {
			$this->ticket_message = $dupe_message;
			return;
		}

		// If status is pending, we'll switch it to open so agents will see it
		if ($this->ticket['status'] == Ticket::STATUS_AWAITING_USER) {
			$this->ticket['status'] = Ticket::STATUS_AWAITING_AGENT;
		}

		App::getOrm()->beginTransaction();
		App::getOrm()->persist($ticket_message);
		App::getOrm()->persist($this->ticket);
		App::getOrm()->flush();
		App::getOrm()->commit();
	}

	public function getNewMessage()
	{
		return $this->ticket_message;
	}
}
