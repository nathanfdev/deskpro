<?php

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
                if ($customDef->getOption('input_type') === 'currency') {
                    $input = $this->faker->numberBetween(100, 20000);
                } elseif ($customDef->getOption('input_type') === 'numeric') {
                    $input = $this->faker->numberBetween($customDef->getOption('min', 1), $customDef->getOption('max', 5));
                } else {
                    $input = $this->faker->realText($this->faker->numberBetween(10, 80));
                }

                $batch[] = array_merge($rowData, [
                    'input' => $input,
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
                    'value' => $this->faker->dateTimeBetween('-2 months', '8 months')->getTimestamp(),
                ]);

                break;
            case CustomDefAbstract::TYPE_TOGGLE:
                $batch[] = array_merge($rowData, [
                    'value' => $this->faker->boolean(),
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
