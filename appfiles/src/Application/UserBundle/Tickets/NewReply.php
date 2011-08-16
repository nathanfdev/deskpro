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

	public $tmp_uploads = array();

	/**
	 * @var \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public $new_upload = null;

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
		$ticket_message['message'] = $this->message;
		$ticket_message->ticket = $this->ticket;
		$ticket_message->person = $this->person;

		if ($id = App::getEntityRepository('DeskPRO:TicketMessage')->checkDupeMessage($ticket_message)) {
			return;
		}

		$attach = false;
		if ($this->new_upload) {
			$desc = App::getApi('filestorage')->createRandomPath();

			$desc->write(file_get_contents($this->new_upload->getRealPath()), array(
				'content_type' => $this->new_upload->getMimeType(),
				'filename' => $this->new_upload->getClientOriginalName()
			));

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$ticket_message->addAttachment($attach);
		}

		$this->ticket->addMessage($ticket_message);

		// If status is pending, we'll switch it to open so agents will see it
		if ($this->ticket['status'] == Ticket::STATUS_PENDING) {
			$this->ticket['status'] = Ticket::STATUS_OPEN;
		}

		App::getOrm()->beginTransaction();
		App::getOrm()->persist($ticket_message);
		if ($attach) {
			App::getOrm()->persist($attach);
		}
		App::getOrm()->persist($this->ticket);
		App::getOrm()->flush();
		App::getOrm()->commit();
	}

	public function getNewMessage()
	{
		return $this->ticket_message;
	}
}