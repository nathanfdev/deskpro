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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Faker\Factory;

/**
 * Class CustomDataGenerator.
 */
class CustomDataGenerator
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @param array             $batch
     * @param CustomDefAbstract $customDef
     * @param string            $ownerProperty
     * @param int               $ownerId
     */
    public function addCustomDefData(array &$batch, CustomDefAbstract $customDef, $ownerProperty, $ownerId)
    {
        $rowData = [
            $ownerProperty  => $ownerId,
            'field_id'      => $customDef->getId(),
            'root_field_id' => $customDef->getId(),
            'value'         => 0,
            'input'         => '',
        ];

        switch ($customDef->getTypeName()) {
            case CustomDefAbstract::TYPE_TEXT:
                $batch[] = array_merge($rowData, [
                    'input' => $this->faker->realText($this->faker->numberBetween(10, 80)),
                ]);

                break;
            case CustomDefAbstract::TYPE_TEXTAREA:
                $batch[] = array_merge($rowData, [
                    'input' => $this->faker->realText($this->faker->numberBetween(20, 500)),
                ]);

                break;
            case CustomDefAbstract::TYPE_DATE:
            case CustomDefAbstract::TYPE_DATETIME:
                $batch[] = array_merge($rowData, [
                    'value' => time(),
                ]);

                break;
            case CustomDefAbstract::TYPE_CHOICE:
                $children = clone $customDef->getChildren();
                $num      = count($children);

                if (!$num) {
                    return;
                }

                if ($customDef->getOption('multiple')) {
                    $num = $this->faker->numberBetween(1, $num);
                } else {
                    $num = 1;
                }

                for ($x = 0; $x < $num; ++$x) {
                    $choice = $this->faker->randomElement($children->toArray());
                    $children->removeElement($choice);

                    $batch[] = array_merge($rowData, [
                        'field_id' => $choice->getId(),
                        'value'    => 1,
                    ]);
                }

                break;
            default:
                throw new \InvalidArgumentException();

        }
    }
}
