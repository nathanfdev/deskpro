<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\CustomDefOrganization;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class OrganizationFieldsFixture.
 */
class OrganizationFieldsFixture extends AbstractCustomDefFixture
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

        //------------------------------
        // Default
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField('select', 'Countries', ['choices' => ['GB', 'USA', 'USSR']]);

        self::$fields['default'] = $fields;
        self::$fields['widgets'] = $this->getWidgetsFields();

        //------------------------------
        // Regulation and Control of Magical Creatures [both]
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'radio',
            'Branches',
            ['choices' => ['London', 'Paris', 'Moscow', 'Madrid', 'Tokyo', 'Other']]
        );

        $fields[] = $this->createField(
            'select',
            'Official position',
            ['choices' => ['CEO', 'Finance Director', 'Senior Developer', 'Useless Man']]
        );

        self::$fields['regulation'] = $fields;
        self::$fields['control']    = $fields;

        //------------------------------
        // Products
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'select',
            'Product',
            [
                'choices' => [
                    'Book',
                    ['Smartphone', ['Apple', ['Samsung', ['Galaxy A5', 'Galaxy Ace']]]],
                    ['Car', ['Toyota', 'Honda', ['Infinity', ['Q50', 'QX70']]]],
                    'Airplane',
                ],
            ]
        );

        $fields[] = $this->createField('textarea', 'Comment', ['default_value' => $this->faker->paragraph]);

        self::$fields['hotdogs'] = $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function initiateEntity()
    {
        return new CustomDefOrganization();
    }
}
