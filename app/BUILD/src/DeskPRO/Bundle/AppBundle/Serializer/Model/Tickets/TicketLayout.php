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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\Department as DepartmentEntity;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField as BaseLayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutField as LayoutFieldModel;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketLayout.
 *
 * @JMS\ExclusionPolicy("none")
 */
class TicketLayout
{
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
        $this->department = $department;
        $this->context    = $context;

        $fields = $this->getOrderedFields($layout, $context);
        foreach ($fields as $field) {
            $this->fields[] = new LayoutFieldModel($field, $context);
        }
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

                foreach ([FormFields::PERSON, FormFields::DEPARTMENT] as $type) {
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
