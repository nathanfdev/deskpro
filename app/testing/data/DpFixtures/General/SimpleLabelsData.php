<?php
namespace DpFixtures\General;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Application\DeskPRO\Entity\LabelDef;

class SimpleLabelsData extends AbstractFixture
{
	public function load(ObjectManager $manager)
	{
		$label1             = new LabelDef();
		$label1->label_type = 'feedback';
		$label1->label      = 'feedback_label1';
		$label1->total      = 5;

		$manager->persist($label1);

		$label2             = new LabelDef();
		$label2->label_type = 'feedback';
		$label2->label      = 'feedback_label2';
		$label2->total      = 10;

		$manager->persist($label2);

		$label3             = new LabelDef();
		$label3->label_type = 'chat';
		$label3->label      = 'chat_label1';
		$label3->total      = 15;

		$manager->persist($label3);

		$label4             = new LabelDef();
		$label4->label_type = 'tickets';
		$label4->label      = 'tickets_label1';
		$label4->total      = 20;

		$manager->persist($label4);

		$manager->flush();
	}
}