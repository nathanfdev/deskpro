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
