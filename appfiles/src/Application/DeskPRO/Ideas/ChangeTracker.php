<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Ideas
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

class ChangeTracker extends \Application\DeskPRO\Domain\ChangeTracker
{
	public function done()
	{
		// No notifications for hidden
		if ($this->entity['status'] == 'hidden') {
			return;
		}

		// Have no one to send notiifcations to
		if (!$this->entity['email']) {
			return;
		}

		$changed_status = $this->getChangedProperty('status');
		$changed_status_cat = $this->getChangedProperty('hidden_status');

		// Nothign happened work reporting
		if (!$changed_status AND !$changed_status_cat) {
			return;
		}

		$old_status = isset($changed_status['old']) ? $changed_status['old'] : null;
		$old_status_cat = isset($changed_status_cat['old']) ? $changed_status_cat['old'] : null;

		$show = null;
		if ($old_status == 'hidden' AND $this->entity['status'] != 'hidden') {
			$show = 'validated';
		} elseif ($this->entity['status'] == 'active') {
			$show = 'updated_active';
		} elseif ($this->entity['status'] == 'closed') {
			$show = 'updated_closed';
		}

		if ($show) {
			$email_subject = "Your idea has been updated: " . $idea['title'];
			$email_body = App::get('templating')->render('DeskPRO:emails_user:idea-updated.html.twig', array(
				'idea' => $idea,
				'changed_status' => $changed_status,
				'changed_status_cat' => $changed_status_cat,
				'show' => $show
			));

			$message = App::getMailer()->createMessage();
			$message->setTo($this->entity['user_email'], $this->entity['user_email']);
			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->enableQueueHint();

			App::getMailer()->send($message);
		}
	}
}
