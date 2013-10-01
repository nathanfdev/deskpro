<?php

class ModelToApiTest extends ContainerTestCase
{
	public function testTicketDepartment()
	{
		#------------------------------
		# Department
		#------------------------------

		$o = new \Application\DeskPRO\Entity\Department();
		$o->id = 1;
		$o->title = 'Title';
		$o->user_title = 'User Title';
		$o->is_tickets_enabled = true;
		$o->is_chat_enabled = false;

		$output = $o->toApiData();

		$this->assertEquals(array(
			'id'                 => 1,
			'title'              => 'Title',
			'user_title'         => 'User Title',
			'is_tickets_enabled' => true,
			'is_chat_enabled'    => false,
			'display_order'      => 0,
			'parent_id'          => null,
			'email_gateway_id'   => null,
		), $output);

		#------------------------------
		# Department with child
		#------------------------------

		$o2 = new \Application\DeskPRO\Entity\Department();
		$o2->id = 2;
		$o2->title = 'Child Title';
		$o2->user_title = 'Child User Title';
		$o2->is_tickets_enabled = true;
		$o2->is_chat_enabled = false;
		$o->addChild($o2);

		$output = $o2->toApiData();

		$this->assertEquals(array(
			'id'                 => 2,
			'title'              => 'Child Title',
			'user_title'         => 'Child User Title',
			'is_tickets_enabled' => true,
			'is_chat_enabled'    => false,
			'display_order'      => 0,
			'parent_id'          => 1,
			'email_gateway_id'   => null,
		), $output);
	}
}