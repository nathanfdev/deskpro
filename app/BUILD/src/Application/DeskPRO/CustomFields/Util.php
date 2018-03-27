<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;
use Orb\Util\Arrays;

/**
 * @deprecated Use the FieldFanager with the field manager service
 */
class Util
{
    /**
     * Standard form field naming has the key as field_X. So this is the same as
     * createDataHierarchy except the key is the standard form name.
     *
     * @return array
     */
    public function createFormData($field_datas, $field_defs)
    {
        $data = $this->createDataHierarchy($field_datas, $field_defs);
        $data = Arrays::walkKeys($data, function (&$k) {
            $k = 'field_'.$k;
        });

        return $data;
    }

    /**
     * This converts a collection of data items into an array structure
     * that matches the hierarchy of field definitions.
     *
     * Custom field values in the database are 'flat', and when displaying values
     * we need to pass a proper structure to a field defition for rendering. This is
     * easy for simple fields like text or textarea, but we need this method for
     * complex fields that have multiple levels, like a date.
     *
     * @see Application\DeskPRO\CustomFields\Handler\HandlerAbstract\renderContext
     *
     * @param $field_datas
     * @param $field_defs
     *
     * @return array
     */
    public function createDataHierarchy($field_datas, $field_defs)
    {
        // Create a map of keys
        $data_keys = [];
        foreach ($field_datas as $k => $v) {
            $data_keys[$v['field']['id']] = $k;
        }

        return $this->_createDataHierarchy($data_keys, $field_datas, $field_defs);
    }

    protected function _createDataHierarchy($data_keys, $field_datas, $field_defs)
    {
        $structure = [];

        foreach ($field_defs as $def) {
            $structure[$def['id']] = ['value' => null, 'children' => null];
            if (isset($data_keys[$def['id']])) {
                $structure[$def['id']]['value'] = $field_datas[$data_keys[$def['id']]]->getData();
            }

            if ($def['children']) {
                $structure[$def['id']]['children'] = $this->_createDataHierarchy($data_keys, $field_datas, $def['children']);
            }
        }

        return $structure;
    }

    /**
     * Use this to get a structured "data array" used with form handlers render(). This essentially emulates
     * created all the data records, and then returns the structured array. So if you need the correct array format,
     * but dont need to store the values in a real data table (eg macros), then you can use this method.
     *
     * @param  $field_id
     * @param array $form_data
     * @param  $entity_def
     * @param  $entity_data
     *
     * @return array
     */
    public function getRenderableDataArrayFromForm(array $form_data, $field_id, $entity_def, $entity_data)
    {
        $field_defs = App::getEntityRepository($entity_def)->getFields();
        $field      = App::getEntityRepository($entity_def)->find($field_id);

        $action_custm_datas = [];

        $data_classname = App::getEntityRepository($entity_data)->getEntityName();

        foreach ($field->getHandler()->getDataFromForm($form_data) as $info) {
            $custom_data           = new $data_classname();
            $custom_data['field']  = $field;
            $custom_data[$info[1]] = $info[2];

            $action_custm_datas[] = $custom_data;
        }

        $data_structured = App::getApi('custom_fields.util')->createDataHierarchy($action_custm_datas, $field_defs);
        $data_structured = $data_structured[$field_id];

        $action['renderable_value'] = $data_structured;
    }
}
