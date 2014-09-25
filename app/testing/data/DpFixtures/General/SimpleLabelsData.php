<?php
namespace DpFixtures\General;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\LabelChatConversation;
use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\LabelFeedback;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class SimpleLabelsData extends AbstractFixture
{
	public function load(ObjectManager $manager)
	{
		$feedback = new Feedback();
		$feedback->title = 'x';
		$feedback->slug = 'x';
		$manager->persist($feedback);
		$manager->flush();

		$chat = new ChatConversation();
		$manager->persist($chat);
		$manager->flush();

		$ticket = new Ticket();
		$ticket->__dp_auto_ticket_process = false;
		$ticket->ticket_hash = 'x';
		$ticket->subject = 'x';
		$manager->persist($ticket);
		$manager->flush();

		$labels = array();
		$label1             = new LabelFeedback();
		$label1->label      = 'feedback_label1';
		$label1->feedback   = $feedback;
		$manager->persist($label1);
		$labels[]           = $label1;

		$label2             = new LabelFeedback();
		$label2->label      = 'feedback_label2';
		$label2->feedback   = $feedback;
		$manager->persist($label2);
		$labels[]           = $label2;


		$label3             = new LabelChatConversation();
		$label3->label      = 'chat_label1';
		$label3->chat       = $chat;
		$manager->persist($label3);
		$labels[]           = $label3;


		$label4             = new LabelTicket();
		$label4->label      = 'tickets_label1';
		$label4->ticket     = $ticket;
		$manager->persist($label4);
		$labels[]           = $label4;

		foreach ($labels as $label) {
			$def = new LabelDef(array(
				'label_type' => substr($label['label'], 0, strpos($label1['label'], '_')),
				'label' => $label['label'],
				'color' => '#cccccc',
			));
			$manager->persist($def);
		}

		$manager->flush();
	}
}