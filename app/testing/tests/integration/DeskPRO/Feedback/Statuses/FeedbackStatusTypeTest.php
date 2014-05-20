<?php
namespace DpIntegrationTests\DeskPRO\Feedback\Statuses;

use Application\DeskPRO\FeedbackStatuses\FeedbackStatusEdit;
use Application\DeskPRO\FeedbackStatuses\Form\Type\FeedbackStatusType;

class FeedbackStatusTypeTest extends \DpIntegrationTestCase
{
	/**
	 * @var \Symfony\Component\Form\Form
	 */

	private $form;

	/**
	 * @var \Application\DeskPRO\Entity\FeedbackStatusCategory
	 */

	private $feedback_status;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDb');
		$this->helper->loadFixtures('General/SimpleFeedbackStatusesData');

		$this->feedback_status = $this->helper->getSymfonyContainer()->getSystemService('feedback_statuses')->getById(1);
		$feedback_status_edit  = new FeedbackStatusEdit($this->feedback_status);
		$this->form            = $this->helper->getSymfonyContainer()->getFormFactory()->create(new FeedbackStatusType(), $feedback_status_edit);
	}

	public function testSuccessfulValidationOfFeedbackStatusForm()
	{
		$this->assertFalse($this->form->isValid());

		$this->form->submit(array('feedback_status' => array('title' => 'test title', 'status_type' => 'active')));

		$this->assertTrue($this->form->isValid());
		$this->assertTrue($this->form->isSynchronized());
	}

	public function testUnsuccessfulValidationOfFeedbackStatusForm()
	{
		$this->assertFalse($this->form->isValid());

		$this->form->submit(array('feedback_status' => array('title' => '', 'status_type' => 'invalid type')));

		$this->assertFalse($this->form->isValid());
	}
}