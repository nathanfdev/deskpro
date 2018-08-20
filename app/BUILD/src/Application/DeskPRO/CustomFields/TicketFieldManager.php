<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefTicket;
use Orb\Util\Strings;

class TicketFieldManager extends FieldManager
{
    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;

    protected function init()
    {
        $this->settings = $this->options->get('settings_handler');
    }

    /**
     * Get a collection of all top-level (parent) fields.
     *
     * @return CustomDefAbstract[]
     */
    public function getFields()
    {
        if ($this->fields === null) {
            $this->fields = [];
            $all_fields   = $this->em->getRepository($this->options->get('entity_name'))->getEnabledFields();

            foreach ($all_fields as $f) {
                $this->all_fields[$f->getId()] = $f;

                if (!$f->getParentId()) {
                    $this->fields[$f->getId()] = $f;
                }

                if ($p = $f->getParentId()) {
                    if (!isset($this->field_to_children[$p])) {
                        $this->field_to_children[$p] = [];
                    }
                    $this->field_to_children[$p][$f->getId()] = $f;
                }
            }

            // Choice fields that have no options are considered disabled
            foreach ($this->fields as $f) {
                if ($f->isChoiceType()) {
                    if (!$this->getFieldChildren($f)) {
                        unset(
                            $this->all_fields[$f->getId()],
                            $this->fields[$f->getId()],
                            $this->field_to_children[$f->getId()]
                        );
                    }
                }
            }
        }

        return $this->fields;
    }

    /**
     * Get an array of all defined fields (by doing a query).
     *
     * @return array
     */
    public function getDefinedFields()
    {
        return array_values($this->em->getRepository(CustomDefTicket::class)->getTopFields());
    }

    public function setCustomDataOnObject($ticket, CustomDefAbstract $fieldDef, array $in_data)
    {
        if (!$ticket->getTicketLogger()) {
            return parent::setCustomDataOnObject($ticket, $fieldDef, $in_data);
        }

        $all_display_data = $this->_orig_display;

        $old_value = null;

        if (isset($all_display_data[$fieldDef->id])) {
            $handler   = $all_display_data[$fieldDef->id]['handler'];
            $old_value = $handler->renderText($all_display_data[$fieldDef->id]['value']);

            if ($old_value) {
                $old_value = trim(str_replace(["\n", "\r\n"], ' ', strip_tags($old_value)));
            }
        }

        $return = parent::setCustomDataOnObject($ticket, $fieldDef, $in_data);

        $new_value = null;
        if ($return) {
            $all_display_data = $this->getDisplayArrayForObject($ticket);
            $handler          = $all_display_data[$fieldDef->id]['handler'];
            $new_value        = $handler->renderText($all_display_data[$fieldDef->id]['value']);
            if ($new_value) {
                $new_value = trim(str_replace(["\n", "\r\n"], ' ', strip_tags($new_value)));
            }
        }

        if (($new_value || $old_value) && ($new_value != $old_value)) {
            $ticket->getTicketLogger()->recordMultiPropertyChanged(
                'custom_data',
                ['field_def' => $fieldDef, 'value' => $old_value],
                ['field_def' => $fieldDef, 'value' => $new_value]
            );
        }

        return $new_value;
    }

    /**
     * @return bool
     */
    public function isProductEnabled()
    {
        return (bool) $this->settings->get('core.use_product');
    }

    /**
     * @return bool
     */
    public function isPriorityEnabled()
    {
        return (bool) $this->settings->get('core.use_ticket_priority');
    }

    /**
     * @return bool
     */
    public function isWorkflowEnabled()
    {
        return (bool) $this->settings->get('core.use_ticket_workflow');
    }

    /**
     * @return bool
     */
    public function isCategoryEnabled()
    {
        return (bool) $this->settings->get('core.use_ticket_category');
    }

    /**
     * @param $enabled bool
     */
    public function setIsProductEnabled($enabled = true)
    {
        $this->settings->setSetting('core.use_product', intval((bool) $enabled));
    }

    /**
     * @param $enabled bool
     */
    public function setIsPriorityEnabled($enabled = true)
    {
        $this->settings->setSetting('core.use_ticket_priority', intval((bool) $enabled));
    }

    /**
     * @param $enabled bool
     */
    public function setIsWorkflowEnabled($enabled = true)
    {
        $this->settings->setSetting('core.use_ticket_workflow', intval((bool) $enabled));
    }

    /**
     * @param $enabled bool
     */
    public function setIsCategoryEnabled($enabled = true)
    {
        $this->settings->setSetting('core.use_ticket_category', intval((bool) $enabled));
    }

    /**
     * @param string $id
     * @param bool   $enabled
     */
    public function setFieldEnabledById($id, $enabled = true)
    {
        if ($custom_field_id = Strings::extractRegexMatch('#^field_(\d+)$#', $id)) {
            $field             = $this->em->find('DeskPRO:CustomDefTicket', $custom_field_id);
            $field->is_enabled = $enabled;
            $this->em->persist($field);
            $this->em->flush();
        } else {
            switch ($id) {
                case 'product':  $this->setIsProductEnabled($enabled); break;
                case 'workflow': $this->setIsWorkflowEnabled($enabled); break;
                case 'priority': $this->setIsPriorityEnabled($enabled); break;
                case 'category': $this->setIsCategoryEnabled($enabled); break;
                default:
                    throw new \Exception('Invalid $id');
            }
        }
    }
}
