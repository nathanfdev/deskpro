<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;

/**
 * @deprecated Use the FieldFanager with the field manager service
 */
class TicketFields extends AbstractFields
{
    const ENTITY_CLASS = 'Application\\DeskPRO\\Entity\\CustomDefTicket';
    const ENTITY_NAME  = 'DeskPRO:CustomDefTicket';

    /**
     * @var \Application\DeskPRO\CustomFields\TicketFieldManager
     */
    private $fm;

    public function __construct()
    {
        $this->fm = App::getContainer()->getSystemService('TicketFieldsManager');
    }

    public function getFieldsDisplayArray($field_defs, $data_structured = [], $field_group = null)
    {
        return $this->fm->getDisplayArray($data_structured, $field_group);
    }

    public function getFields()
    {
        return $this->fm->getFields();
    }

    public function getEnabledFields()
    {
        return $this->fm->getFields();
    }

    public function getFieldFromId($field_id)
    {
        return $this->fm->getFieldFromId($field_id);
    }
}
