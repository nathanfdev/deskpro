<?php
namespace DpUnitTests\DeskPRO\Feedback\Statuses\Entity;

use Application\DeskPRO\Entity\FeedbackStatusCategory;

class FeedbackStatusCategoryEntityTest extends \DpUnitTestCase
{
    /**
     * @var \Application\DeskPRO\Entity\FeedbackStatusCategory
     */

    private $entity;

    /**
     * @var \Symfony\Component\Validator\Validator
     */

    private $validator;

    public function runBefore()
    {
        $this->entity    = new FeedbackStatusCategory();
        $this->validator = $this->helper->getSymfonyContainer()->getValidator();
    }

    public function testSuccessfulValidationOfFeedbackStatusCategoryEntity()
    {
        $this->entity->title       = 'test title';
        $this->entity->status_type = 'active';

        $errors = $this->validator->validate($this->entity);
        $this->assertEquals(0, sizeof($errors));
    }

    public function testUnsuccessfulValidationOfFeedbackStatusCategoryEntity()
    {
        $this->entity->title       = '';
        $this->entity->status_type = 'some invalid type';

        $errors = $this->validator->validate($this->entity);

        $this->assertEquals(2, sizeof($errors));
        $this->assertNotSame(false, strpos($errors[0]->getMessage(), 'This value should not be blank'));
        $this->assertNotSame(false, strpos($errors[1]->getMessage(), 'The value you selected is not a valid choice'));
    }
}
