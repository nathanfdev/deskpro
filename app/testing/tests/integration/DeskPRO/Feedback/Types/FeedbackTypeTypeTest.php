<?php
namespace DpIntegrationTests\DeskPRO\Feedback\Types;

use Application\DeskPRO\FeedbackTypes\FeedbackTypeEdit;
use Application\DeskPRO\FeedbackTypes\Form\Type\FeedbackTypeType;

class FeedbackTypeTypeTest extends \DpIntegrationTestCase
{
	/**
	 * @var \Symfony\Component\Form\Form
	 */

	private $form;

	/**
	 * @var \Application\DeskPRO\Entity\FeedbackCategory
	 */

	private $feedback_type;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDb');
		$this->helper->loadFixtures('General/FeedbackTypesWithUsergroupsData');

		$this->feedback_type = $this->helper->getSymfonyContainer()->getSystemService('feedback_types')->getById(1);
		$feedback_type_edit  = new FeedbackTypeEdit($this->feedback_type);
		$this->form          = $this->helper->getSymfonyContainer()->getFormFactory()->create(new FeedbackTypeType(), $feedback_type_edit);
	}

	public function testSuccessfulValidationOfFeedbackTypeForm()
	{
		$this->assertFalse($this->form->isValid());

		$this->form->submit(array('feedback_type' => array('title' => 'test title')));

		$this->assertTrue($this->form->isValid());
		$this->assertTrue($this->form->isSynchronized());
	}

	public function testUnsuccessfulValidationOfFeedbackTypeForm()
	{
		$this->assertFalse($this->form->isValid());

		$this->form->submit(array('feedback_type' => array('title' => '')));

		$this->assertFalse($this->form->isValid());
	}
}