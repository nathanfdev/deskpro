<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDataOrganization;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Organization;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

/**
 * Class AbstractCustomDefFixture.
 */
abstract class AbstractCustomDefFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    protected $cnt = 1;

    protected $customPersonChoiceFields = [];

    protected $customOrgChoiceFields = [];

    /**
     * @return CustomDefAbstract
     */
    abstract protected function initiateEntity();

    /**
     * @param string $type
     * @param string $title
     * @param array  $options
     *
     * @return CustomDefAbstract
     */
    protected function createField($type, $title, array $options = [])
    {
        $handlers = 'Application\DeskPRO\CustomFields\Handler\\';
        switch ($type) {
            case 'text':
                $handlerClass = $handlers.'Text';
                break;
            case 'textarea':
                $handlerClass = $handlers.'Textarea';
                break;
            case 'date':
                $handlerClass = $handlers.'Date';
                break;
            case 'datetime':
                $handlerClass = $handlers.'DateTime';
                break;
            case 'select':
                $handlerClass = $handlers.'Choice';
                break;
            case 'multiselect':
                $handlerClass        = $handlers.'Choice';
                $options['multiple'] = true;
                break;
            case 'checkbox':
                $handlerClass        = $handlers.'Choice';
                $options['multiple'] = true;
                $options['expanded'] = true;
                break;
            case 'radio':
                $handlerClass        = $handlers.'Choice';
                $options['multiple'] = false;
                $options['expanded'] = true;
                break;
            default:
                throw new \InvalidArgumentException();
        }

        $f = $this->initiateEntity();
        $f
            ->setTitle($title)
            ->setDescription('A custom '.$f->getWidgetType().' field')
            ->setHandlerClass($handlerClass)
            ->setOptions($options)
            ->setIsUserEnabled(true)
            ->setIsEnabled(true)
            ->setDisplayOreder($this->cnt++);

        if ($handlerClass !== $handlers.'Choice' && array_key_exists('default_value', $options)) {
            $f->setDefaultValue($options['default_value']);
        }

        $this->manager->persist($f);

        if ($handlerClass === $handlers.'Choice' && array_key_exists('choices', $options)) {
            foreach ($options['choices'] as $c) {
                $this->createSubOptions($f, null, $c);
            }
        } else {
            if ($this instanceof PersonFieldsFixture) {
                foreach ($this->getPersons() as $person) {
                    $customData = $this->createCustomDataPerson($person);
                    $this->setUpCustomInputData($type, $customData, $f);
                }
            } elseif ($this instanceof OrganizationFieldsFixture) {
                $customData = $this->createCustomDataOrganization();
                $this->setUpCustomInputData($type, $customData, $f);
            }
        }
        $this->manager->flush();

        return $f;
    }

    /**
     * @return array
     */
    protected function getWidgetsFields()
    {
        //------------------------------
        // Widgets
        //------------------------------
        $fields   = [];
        $fields[] = $this->createField('text', 'Widget Type', ['default_value' => $this->faker->word]);
        $fields[] = $this->createField('textarea', 'Widget Description', ['default_value' => $this->faker->paragraph]);
        $fields[] = $this->createField('checkbox', 'Desired Sizes', ['choices' => ['Small', 'Medium', 'Large']]);
        $fields[] = $this->createField('date', 'Manufacture Date');

        return $fields;
    }

    /**
     * @param CustomDefAbstract      $parent
     * @param CustomDefAbstract|null $parentOption
     * @param array|string           $desc
     *
     * @return CustomDefAbstract
     */
    private function createSubOptions(CustomDefAbstract $parent, CustomDefAbstract $parentOption = null, $desc)
    {
        if (is_array($desc)) {
            $title  = $desc[0];
            $others = $desc[1];
        } else {
            $title  = $desc;
            $others = [];
        }

        $optionField = $this->initiateEntity();
        $optionField
            ->setTitle($title)
            ->setDescription('')
            ->setIsUserEnabled(true)
            ->setIsEnabled(true)
            ->setDisplayOreder($this->cnt++)
            ->setParent($parent);

        if ($parentOption) {
            $optionField->setOption('parent_id', $parentOption->getId());
        }

        $parent->addChild($optionField);

        $this->manager->persist($optionField);

        if ($others) {
            foreach ($others as $subTitle) {
                $this->createSubOptions($parent, $optionField, $subTitle);
            }
        } else {
            $this->manager->flush();
            $id = $optionField->getId();
            $parent->setDefaultValue($id);
            if ($this instanceof PersonFieldsFixture) {
                $refName                          = 'person.custom_field_'.$id;
                $this->customPersonChoiceFields[] = $refName;
                $this->setReference($refName, $optionField);
            } elseif ($this instanceof OrganizationFieldsFixture) {
                $refName                       = 'org.custom_field_'.$id;
                $this->customOrgChoiceFields[] = $refName;
                $this->setReference($refName, $optionField);
            }
        }

        return $optionField;
    }

    protected function getPersons()
    {
        return [
            $this->getReference('person.publisher'),
            $this->getReference('person.joe'),
            $this->getReference('person.joes_manager'),
        ];
    }

    /**
     * @param $person
     *
     * @return CustomDataPerson
     */
    protected function createCustomDataPerson($person)
    {
        $customData = new CustomDataPerson();
        $customData->setPerson($person);

        return $customData;
    }

    /**
     * @return CustomDataOrganization
     */
    protected function createCustomDataOrganization()
    {
        /** @var Organization $organization */
        $organization = $this->getReference('org.mana');
        $customData   = new CustomDataOrganization();
        $customData->setOrganization($organization);

        return $customData;
    }

    /**
     * @param string             $type
     * @param CustomDataAbstract $customData
     * @param CustomDefAbstract  $f
     */
    private function setUpCustomInputData($type, CustomDataAbstract $customData, CustomDefAbstract $f)
    {
        $customData
            ->setRootField($f)
            ->setField($f)
            ->setValue(0);
        if ($type === CustomDefAbstract::TYPE_TEXT) {
            $customData
                ->setInput($this->faker->words(3, true));
        } elseif ($type === CustomDefAbstract::TYPE_TEXTAREA) {
            $customData
                ->setInput($this->faker->paragraph());
        } elseif (array_search($type, [CustomDefAbstract::TYPE_DATE, CustomDefAbstract::TYPE_DATETIME])) {
            $customData
                ->setValue($this->faker->dateTimeBetween()->getTimestamp());
        }
        $this->manager->persist($customData);
    }

    /**
     * @param CustomDefAbstract  $parent
     * @param CustomDataAbstract $customData
     * @param CustomDefAbstract  $optionField
     */
    protected function setUpCustomChoiceData(
        CustomDefAbstract $parent,
        CustomDataAbstract $customData,
        CustomDefAbstract $optionField
    ) {
        $customData
            ->setRootField($parent)
            ->setField($optionField)
            ->setValue(1);
        $this->manager->persist($customData);
    }
}
