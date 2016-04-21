<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields;

use Application\DeskPRO\Entity\CustomDefPerson;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class PersonFieldsFixture.
 */
class PersonFieldsFixture extends AbstractCustomDefFixture
{
    public static $fields = [];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 60;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        #------------------------------
        # Default
        #------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'radio',
            'Owner',
            ['Mine', 'Not mine', "I don't know"]
        );

        self::$fields['default'] = $fields;

        #------------------------------
        # Widgets
        #------------------------------
        $fields   = [];
        $fields[] = $this->createField('text', 'Widget Type');
        $fields[] = $this->createField('textarea', 'Widget Description');
        $fields[] = $this->createField('checkbox', 'Desired Sizes', ['Small', 'Medium', 'Large']);
        $fields[] = $this->createField('date', 'Manufacture Date');

        self::$fields['widgets'] = $fields;

        #------------------------------
        # Regulation and Control of Magical Creatures [both]
        #------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'select',
            'Mood today',
            ['Fun', 'Serious', 'Lyric', 'Furious', 'Impassive', 'Other']
        );

        $fields[] = $this->createField(
            'multiselect',
            'Dishes',
            ['Steak', 'Burger', 'Porridge', 'Tom Yam soup']
        );

        self::$fields['regulation'] = $fields;
        self::$fields['control']    = $fields;

        #------------------------------
        # Music
        #------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'select',
            'Music',
            [
                'Classic',
                ['Rock', ['Nazareth', 'Deep Purple', ['Queen', ['We will rock you', 'Bohemian Rhapsody']]]],
                'Jazz',
            ]
        );

        $fields[] = $this->createField('datetime', 'Date of Creation');

        self::$fields['hotdogs'] = $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function initiateEntity()
    {
        return new CustomDefPerson();
    }
}
