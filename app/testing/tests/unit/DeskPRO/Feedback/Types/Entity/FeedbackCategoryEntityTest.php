<?php
namespace DpUnitTests\DeskPRO\Feedback\Statuses\Entity;

use Application\DeskPRO\Entity\FeedbackCategory;

class FeedbackCategoryEntityTest extends \DpUnitTestCase
{
	/**
	 * @var \Application\DeskPRO\Entity\FeedbackCategory
	 */

	private $entity;

	/**
	 * @var \Symfony\Component\Validator\Validator
	 */

	private $validator;

	public function runBefore()
	{
		$this->entity    = new FeedbackCategory();
		$this->validator = $this->helper->getSymfonyContainer()->getValidator();
	}

	public function testSuccessfulValidationOfFeedbackCategoryEntity()
	{
		$this->entity->title = 'test title';

		$errors = $this->validator->validate($this->entity);
		$this->assertEquals(0, sizeof($errors));
	}

	public function testUnsuccessfulValidationOfFeedbackCategoryEntity()
	{
		$this->entity->title = '';

		$errors = $this->validator->validate($this->entity);

		$this->assertEquals(1, sizeof($errors));
		$this->assertNotSame(false, strpos($errors[0]->getMessage(), 'This value should not be blank'));
	}

	public function testUsergroupsForFeedbackCategoryEntity()
	{
		$usergroup1 = $this->getMock('\Application\DeskPRO\Entity\Usergroup');
		$usergroup2 = $this->getMock('\Application\DeskPRO\Entity\Usergroup');

		$this->entity->addUsergroup($usergroup1);
		$this->entity->addUsergroup($usergroup2);

		$this->assertEquals(2, sizeof($this->entity->getUserGroups()));

		$this->entity->removeUsergroup($usergroup1);

		$this->assertEquals(1, sizeof($this->entity->getUserGroups()));
	}
}