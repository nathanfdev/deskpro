<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Orb\Util\Arrays;

class TicketAttachment extends \Doctrine\ORM\EntityRepository
{
	/**
	 * Get attachments for a ticket
	 *
	 * @param  $ticket
	 * @return array
	 */
	public function getTicketAttachments($ticket)
	{
		$attachments = $this->getEntityManager()->createQuery("
			SELECT a, p, m
			FROM DeskPRO:TicketAttachment a INDEX BY a.id
			LEFT JOIN a.person p
			LEFT JOIN a.message m
			LEFT JOIN a.blob b
			WHERE a.ticket = ?1
			ORDER BY a.id DESC
		")->setParameter(1, $ticket)->execute();

		return $attachments;
	}

	public function getAttachmentsForMessages($messages)
	{
		if (!$messages) {
			return array();
		}

		$message_ids = array();
		foreach ($messages as $m) {
			$message_ids[] = $m['id'];
		}

		$message_ids = implode(',', $message_ids);

		$attachments = $this->getEntityManager()->createQuery("
			SELECT a, b
			FROM DeskPRO:TicketAttachment a INDEX BY a.id
			LEFT JOIN a.blob b
			WHERE a.message IN ($message_ids)
			ORDER BY a.id DESC
		")->execute();

		return $attachments;
	}
}