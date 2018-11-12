<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\Department as DepartmentEntity;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField as BaseLayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutField as LayoutFieldModel;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketLayout.
 */
class TicketLayout
{
    /**
     * @JMS\Exclude()
     *
     * @var Layout
     */
    private $layout;

    /**
     * Department uses this layout.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var DepartmentEntity
     */
    private $department;

    /**
     * An array of fields describing layout.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutField>")
     *
     * @var LayoutFieldModel[]
     */
    private $fields = [];

    /**
     * Agent or user context.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $context;

    /**
     * Constructor.
     *
     * @param Layout           $layout
     * @param string           $context
     * @param DepartmentEntity $department
     */
    public function __construct(Layout $layout, $context, DepartmentEntity $department = null)
    {
        $this->layout     = $layout;
        $this->department = $department;
        $this->context    = $context;

        $fields = $this->getOrderedFields($layout, $context);
        foreach ($fields as $field) {
            $this->fields[] = new LayoutFieldModel($field, $context);
        }
    }

    /**
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return int|null
     */
    public function getDepartmentId()
    {
        return $this->department ? $this->department->getId() : 'default';
    }

    /**
     * Reorder fields for agent layout.
     *
     * Person
     * Department
     * All other layout fields as normal
     * Subject
     * Message / attach
     *
     * @param Layout $layout
     * @param string $context
     *
     * @return BaseLayoutField[]
     */
    private function getOrderedFields(Layout $layout, $context)
    {
        /** @var BaseLayoutField[] $fields */
        $fields = array_values($layout->all());

        if ($context === 'agent') {
            uksort($fields, function ($aKey, $bKey) use ($fields) {
                $a = $fields[$aKey];
                $b = $fields[$bKey];

                $aType = $a->getFieldType();
                $bType = $b->getFieldType();

                foreach ([FormFields::PERSON, FormFields::BRAND, FormFields::DEPARTMENT] as $type) {
                    if ($aType === $type) {
                        return -1;
                    }
                    if ($bType === $type) {
                        return 1;
                    }
                }
                foreach ([FormFields::ATTACHMENTS, FormFields::MESSAGE, FormFields::SUBJECT] as $type) {
                    if ($aType === $type) {
                        return 1;
                    }
                    if ($bType === $type) {
                        return -1;
                    }
                }

                return $aKey > $bKey ? 1 : -1;
            });
        }

        return array_values($fields);
    }
}
