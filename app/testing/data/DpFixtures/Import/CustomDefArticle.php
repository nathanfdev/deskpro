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

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CustomDefArticle.
 */
class CustomDefArticle extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->addTextField($manager);
        $this->addTextareaField($manager);
        $this->addSelectBoxField($manager);
        $this->addMultipleSelectBoxField($manager);
        $this->addRadioButtonField($manager);
        $this->addCheckboxField($manager);
        $this->addToggleField($manager);
        $this->addDateField($manager);
        $this->addDatetimeField($manager);
        $this->addDisplayField($manager);
        $this->addHiddenField($manager);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function addTextField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Text field')
            ->setDescription('Text field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_TEXT)
        ;

        $manager->persist($ticket_def);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addTextareaField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Textarea field')
            ->setDescription('Textarea field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_TEXTAREA)
        ;

        $manager->persist($ticket_def);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addSelectBoxField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Custom ticket select box field')
            ->setDescription('Select box field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_CHOICE)
        ;

        $ticket_def_choice1 = new Entity\CustomDefArticle();
        $ticket_def_choice1
            ->setTitle('Choice 1')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice2 = new Entity\CustomDefArticle();
        $ticket_def_choice2
            ->setTitle('Choice 2')
            ->setParent($ticket_def)
        ;

        $manager->persist($ticket_def);
        $manager->persist($ticket_def_choice1);
        $manager->persist($ticket_def_choice2);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addMultipleSelectBoxField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Multiple-select box field')
            ->setDescription('Multiple-select box field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_CHOICE)
        ;

        $ticket_def_choice1 = new Entity\CustomDefArticle();
        $ticket_def_choice1
            ->setTitle('Choice 1')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice2 = new Entity\CustomDefArticle();
        $ticket_def_choice2
            ->setTitle('Choice 2')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice3 = new Entity\CustomDefArticle();
        $ticket_def_choice3
            ->setTitle('Choice 3')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice4 = new Entity\CustomDefArticle();
        $ticket_def_choice4
            ->setTitle('Choice 4')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice5 = new Entity\CustomDefArticle();
        $ticket_def_choice5
            ->setTitle('Choice 5')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice6 = new Entity\CustomDefArticle();
        $ticket_def_choice6
            ->setTitle('Choice 6')
            ->setParent($ticket_def)
        ;

        $manager->persist($ticket_def);
        $manager->persist($ticket_def_choice1);
        $manager->persist($ticket_def_choice2);
        $manager->persist($ticket_def_choice3);
        $manager->persist($ticket_def_choice4);
        $manager->persist($ticket_def_choice5);
        $manager->persist($ticket_def_choice6);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addRadioButtonField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Radio button field')
            ->setDescription('Radio button field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_CHOICE)
        ;

        $ticket_def_choice1 = new Entity\CustomDefArticle();
        $ticket_def_choice1
            ->setTitle('Choice 1')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice2 = new Entity\CustomDefArticle();
        $ticket_def_choice2
            ->setTitle('Choice 2')
            ->setParent($ticket_def)
        ;

        $manager->persist($ticket_def);
        $manager->persist($ticket_def_choice1);
        $manager->persist($ticket_def_choice2);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addCheckboxField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Checkbox field')
            ->setDescription('Checkbox field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_CHOICE)
        ;

        $ticket_def_choice1 = new Entity\CustomDefArticle();
        $ticket_def_choice1
            ->setTitle('Choice 1')
            ->setParent($ticket_def)
        ;

        $ticket_def_choice2 = new Entity\CustomDefArticle();
        $ticket_def_choice2
            ->setTitle('Choice 2')
            ->setParent($ticket_def)
        ;

        $manager->persist($ticket_def);
        $manager->persist($ticket_def_choice1);
        $manager->persist($ticket_def_choice2);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addToggleField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Toggle field')
            ->setDescription('Toggle field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_TOGGLE)
        ;

        $manager->persist($ticket_def);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addDateField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Date field')
            ->setDescription('Date field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_DATE)
        ;

        $manager->persist($ticket_def);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addDatetimeField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Datetime field')
            ->setDescription('Datetime field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_DATETIME)
        ;

        $manager->persist($ticket_def);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addDisplayField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Display field')
            ->setDescription('Display field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_DISPLAY)
        ;

        $manager->persist($ticket_def);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addHiddenField(ObjectManager $manager)
    {
        $ticket_def = new Entity\CustomDefArticle();
        $ticket_def
            ->setTitle('Hidden field')
            ->setDescription('Hidden field description')
            ->setHandlerClass(Entity\CustomDefArticle::HANDLER_CLASS_HIDDEN)
        ;

        $manager->persist($ticket_def);
    }
}
