<?php

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CustomDefOrganization
 * @package DpFixtures\Import
 */
class CustomDefOrganization extends AbstractFixture
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
     * @return void
     */
    private function addTextField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Text field')
            ->setDescription('Text field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_TEXT)
        ;

        $manager->persist($organization_def);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addTextareaField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Textarea field')
            ->setDescription('Textarea field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_TEXTAREA)
        ;

        $manager->persist($organization_def);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addSelectBoxField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Custom ticket select box field')
            ->setDescription('Select box field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_CHOICE)
        ;

        $organization_def_choice1 = new Entity\CustomDefOrganization();
        $organization_def_choice1
            ->setTitle('Choice 1')
            ->setParent($organization_def)
        ;

        $organization_def_choice2 = new Entity\CustomDefOrganization();
        $organization_def_choice2
            ->setTitle('Choice 2')
            ->setParent($organization_def)
        ;

        $manager->persist($organization_def);
        $manager->persist($organization_def_choice1);
        $manager->persist($organization_def_choice2);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addMultipleSelectBoxField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Multiple-select box field')
            ->setDescription('Multiple-select box field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_CHOICE)
        ;

        $organization_def_choice1 = new Entity\CustomDefOrganization();
        $organization_def_choice1
            ->setTitle('Choice 1')
            ->setParent($organization_def)
        ;

        $organization_def_choice2 = new Entity\CustomDefOrganization();
        $organization_def_choice2
            ->setTitle('Choice 2')
            ->setParent($organization_def_choice1)
        ;

        $organization_def_choice3 = new Entity\CustomDefOrganization();
        $organization_def_choice3
            ->setTitle('Choice 3')
            ->setParent($organization_def_choice1)
        ;

        $organization_def_choice4 = new Entity\CustomDefOrganization();
        $organization_def_choice4
            ->setTitle('Choice 4')
            ->setParent($organization_def)
        ;

        $organization_def_choice5 = new Entity\CustomDefOrganization();
        $organization_def_choice5
            ->setTitle('Choice 5')
            ->setParent($organization_def_choice4)
        ;

        $organization_def_choice6 = new Entity\CustomDefOrganization();
        $organization_def_choice6
            ->setTitle('Choice 6')
            ->setParent($organization_def_choice4)
        ;

        $manager->persist($organization_def);
        $manager->persist($organization_def_choice1);
        $manager->persist($organization_def_choice2);
        $manager->persist($organization_def_choice3);
        $manager->persist($organization_def_choice4);
        $manager->persist($organization_def_choice5);
        $manager->persist($organization_def_choice6);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addRadioButtonField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Radio button field')
            ->setDescription('Radio button field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_CHOICE)
        ;

        $organization_def_choice1 = new Entity\CustomDefOrganization();
        $organization_def_choice1
            ->setTitle('Choice 1')
            ->setParent($organization_def)
        ;

        $organization_def_choice2 = new Entity\CustomDefOrganization();
        $organization_def_choice2
            ->setTitle('Choice 2')
            ->setParent($organization_def)
        ;

        $manager->persist($organization_def);
        $manager->persist($organization_def_choice1);
        $manager->persist($organization_def_choice2);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addCheckboxField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Checkbox field')
            ->setDescription('Checkbox field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_CHOICE)
        ;

        $organization_def_choice1 = new Entity\CustomDefOrganization();
        $organization_def_choice1
            ->setTitle('Choice 1')
            ->setParent($organization_def)
        ;

        $organization_def_choice2 = new Entity\CustomDefOrganization();
        $organization_def_choice2
            ->setTitle('Choice 2')
            ->setParent($organization_def)
        ;

        $manager->persist($organization_def);
        $manager->persist($organization_def_choice1);
        $manager->persist($organization_def_choice2);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addToggleField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Toggle field')
            ->setDescription('Toggle field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_TOGGLE)
        ;

        $manager->persist($organization_def);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addDateField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Date field')
            ->setDescription('Date field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_DATE)
        ;

        $manager->persist($organization_def);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addDatetimeField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Datetime field')
            ->setDescription('Datetime field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_DATETIME)
        ;

        $manager->persist($organization_def);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addDisplayField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Display field')
            ->setDescription('Display field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_DISPLAY)
        ;

        $manager->persist($organization_def);
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function addHiddenField(ObjectManager $manager)
    {
        $organization_def = new Entity\CustomDefOrganization();
        $organization_def
            ->setTitle('Hidden field')
            ->setDescription('Hidden field description')
            ->setHandlerClass(Entity\CustomDefOrganization::HANDLER_CLASS_HIDDEN)
        ;

        $manager->persist($organization_def);
    }
}
