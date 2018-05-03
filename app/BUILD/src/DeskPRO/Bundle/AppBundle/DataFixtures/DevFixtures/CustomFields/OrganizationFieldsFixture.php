<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
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

        $customData = $this->createCustomDataOrganization();
        foreach ($this->customOrgChoiceFields as $ref) {
            /** @var CustomDefAbstract $customOrgChoiceField */
            $customOrgChoiceField = $this->getReference($ref);
            $this->setUpCustomChoiceData(
                $customOrgChoiceField->getParent(),
                $customData,
                $customOrgChoiceField
            );
        }
        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function initiateEntity()
    {
        return new CustomDefOrganization();
    }
}
