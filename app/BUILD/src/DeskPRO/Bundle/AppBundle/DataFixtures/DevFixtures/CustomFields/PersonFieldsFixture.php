<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
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

        //------------------------------
        // Default
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField('radio', 'Owner', ['choices' => ['Mine', 'Not mine', "I don't know"]]);

        self::$fields['default'] = $fields;
        self::$fields['widgets'] = $this->getWidgetsFields();

        //------------------------------
        // Regulation and Control of Magical Creatures [both]
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'select',
            'Mood today',
            ['choices' => ['Fun', 'Serious', 'Lyric', 'Furious', 'Impassive', 'Other']]
        );

        $fields[] = $this->createField(
            'multiselect',
            'Dishes',
            ['choices' => ['Steak', 'Burger', 'Porridge', 'Tom Yam soup']]
        );

        self::$fields['regulation'] = $fields;
        self::$fields['control']    = $fields;

        //------------------------------
        // Music
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField(
            'select',
            'Music',
            [
                'choices' => [
                    'Classic',
                    ['Rock', ['Nazareth', 'Deep Purple', ['Queen', ['We will rock you', 'Bohemian Rhapsody']]]],
                    'Jazz',
                ],
            ]
        );

        $fields[] = $this->createField('datetime', 'Date of Creation');

        self::$fields['hotdogs'] = $fields;
        foreach ($this->getPersons() as $person) {
            foreach ($this->customPersonChoiceFields as $ref) {
                /** @var CustomDefAbstract $customPersonChoiceField */
                $customPersonChoiceField = $this->getReference($ref);
                $customData              = $this->createCustomDataPerson($person);
                $this->setUpCustomChoiceData(
                    $customPersonChoiceField->getParent(),
                    $customData,
                    $customPersonChoiceField
                );
            }
        }
        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function initiateEntity()
    {
        return new CustomDefPerson();
    }
}
