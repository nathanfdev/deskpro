<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields;

use Application\DeskPRO\Entity\CustomDefTicket;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class TicketFieldsFixture.
 */
class TicketFieldsFixture extends AbstractCustomDefFixture
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
        $fields[] = $this->createField(
            'select',
            'Flumdiggler',
            ['choices' => ['Agree', 'Disagree', 'I\'d rather not say']]
        );

        self::$fields['default'] = $fields;
        self::$fields['widgets'] = $this->getWidgetsFields();

        //------------------------------
        // Regulation and Control of Magical Creatures [both]
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'radio',
            'Reason for Complaint',
            ['choices' => ['Nuisance', 'Dangerous', 'Smelly', 'Ugly', 'Mean', 'Other']]
        );

        $fields[] = $this->createField(
            'multiselect',
            'Suggested Actions',
            ['choices' => ['Eviction', 'Shun', 'Fire them off to the moon', 'Strongly worded letter']]
        );

        self::$fields['regulation'] = $fields;
        self::$fields['control']    = $fields;

        //------------------------------
        // Hotdogs
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'select',
            'Hotdog Kind',
            [
                'choices' => [
                    'Normal',
                    ['German', ['Bratwurst', 'Extrawurst', ['Frankfurter', ['Rindswurst', 'Würstchen']]]],
                    'Large',
                ],
            ]
        );

        $fields[] = $this->createField('datetime', 'Delivery Time');

        self::$fields['hotdogs'] = $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function initiateEntity()
    {
        return new CustomDefTicket();
    }
}
