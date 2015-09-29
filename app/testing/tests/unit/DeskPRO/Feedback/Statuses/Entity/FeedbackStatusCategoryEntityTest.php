<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
