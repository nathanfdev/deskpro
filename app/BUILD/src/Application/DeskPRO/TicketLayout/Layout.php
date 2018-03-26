<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\Entity\Ticket;
use JMS\Serializer\Annotation as JMS;
use Orb\Types\JsonObjectSerializable;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * Class Layout.
 *
 * @JMS\ExclusionPolicy("all")
 */
class Layout implements \IteratorAggregate, \Serializable, JsonObjectSerializable
{
    /**
     * Fields describing this layout.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var LayoutField[]
     */
    private $fields = [];

    /**
     * @param array $fields
     */
    public function setAll(array $fields)
    {
        $this->fields = [];
        foreach ($fields as $f) {
            $this->add($f);
        }
    }

    /**
     * Create a new Layout by filtering fields through $fn. $fn must return true for a field to be added to the new layout.
     *
     * @param callable $fn
     *
     * @return Layout
     */
    public function filter($fn)
    {
        $layout = new self();
        foreach ($this->fields as $f) {
            if (call_user_func($fn, $f) === true) {
                $layout->add($f);
            }
        }

        return $layout;
    }

    /**
     * @param LayoutField $field
     * @param string      $before_field Field ID of a field to insert the field before. If not specified, the field is added to the end
     *
     * @return $this
     */
    public function add(LayoutField $field, $before_field = null)
    {
        $id = $field->getId();
        if (isset($this->fields[$id])) {
            unset($this->fields[$id]);
        }

        $did_add = false;
        if ($before_field) {
            $pos = Arrays::findKey($this->fields, function (LayoutField $v) use ($before_field) {
                return $v->getId() == $before_field;
            });
            if ($pos !== null) {
                $all_fields   = $this->fields;
                $this->fields = [];
                foreach ($all_fields as $k => $v) {
                    if ($k == $before_field) {
                        $did_add           = true;
                        $this->fields[$id] = $field;
                    }

                    $this->fields[$k] = $v;
                }
            }
        }

        if (!$did_add) {
            $this->fields[$id] = $field;
        }

        return $this;
    }

    /**
     * @param string $id
     * @param string $toId
     * @param bool   $append
     *
     * @return $this
     */
    public function moveField($id, $toId, $append = true)
    {
        if (isset($this->fields[$id]) && isset($this->fields[$toId])) {
            $moveField = $this->fields[$id];
            unset($this->fields[$id]);

            $oldFields    = $this->fields;
            $this->fields = [];

            foreach ($oldFields as $k => $field) {
                // prepend field
                if (!$append) {
                    if ($field->getId() === $toId) {
                        $this->fields[$id] = $moveField;
                    }
                }

                $this->fields[$k] = $field;

                // append field
                if ($append) {
                    if ($field->getId() === $toId) {
                        $this->fields[$id] = $moveField;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param LayoutField $field
     */
    public function prepend(LayoutField $field)
    {
        $id = $field->getId();
        if (isset($this->fields[$id])) {
            unset($this->fields[$id]);
        }

        Arrays::unshiftAssoc($this->fields, $id, $field);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->fields[$id]);
    }

    /**
     * @param string $id
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function hasActiveField($id, Ticket $ticket)
    {
        if (!isset($this->fields[$id])) {
            return false;
        }

        $f = $this->fields[$id];
        if ($f->getCriteria() && !$f->getCriteria()->isTicketMatch($ticket)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $id
     *
     * @return LayoutField
     */
    public function get($id)
    {
        if (!isset($this->fields[$id])) {
            throw new \InvalidArgumentException("Invalid field ID: $id");
        }

        return $this->fields[$id];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * @return LayoutField[]
     */
    public function all()
    {
        return $this->fields;
    }

    /**
     * @param string $id
     */
    public function remove($id)
    {
        unset($this->fields[$id]);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * @return string
     */
    public function compileJsObj()
    {
        $js = "(function () {\n";
        $js .= "\tvar fields = [\n";

        $fields_js = [];
        foreach ($this->fields as $field) {
            if ($field->hasCriteria()) {
                $check_fn = trim(Strings::modifyLines($field->compileJsCheck(), "\t\t\t\t"));
            } else {
                $check_fn = 'null';
            }

            $bit_js = "\t\t{\n";
            $bit_js .= "\t\t\tid:                    '{$field->getId()}',\n";
            $bit_js .= "\t\t\tfield_type:            '{$field->getFieldType()}',\n";
            $bit_js .= "\t\t\tfield_id:              ".($field->getFieldId() ? "'{$field->getFieldId()}'" : 'null').",\n";
            $bit_js .= "\t\t\tisVisibleOnNew:        ".($field->isVisibleOnNew() ? 'true' : 'false').",\n";
            $bit_js .= "\t\t\tisVisibleOnView:       ".($field->isVisibleOnView() ? 'true' : 'false').",\n";
            $bit_js .= "\t\t\tisVisibleOnViewAlways: ".($field->isVisibleOnViewAlways() ? 'true' : 'false').",\n";
            $bit_js .= "\t\t\tisVisibleOnEdit:       ".($field->isVisibleOnEdit() ? 'true' : 'false').",\n";
            $bit_js .= "\t\t\tcheckFn:               $check_fn\n";
            $bit_js .= "\t\t}";
            $fields_js[] = $bit_js;
        }

        $js .= implode(",\n", $fields_js)."\n\t];\n\n";

        $js .= "\treturn {\n";
        $js .= "\t\tgetMatchingFields: function (ticket, asString) {\n";
        $js .= "\t\t\tif (typeof getReader === 'function') {\n";
        $js .= "\t\t\t\tticket = getReader(ticket);\n";
        $js .= "\t\t\t}\n";

        $js .= "\t\t\tvar match = [];\n";
        $js .= "\t\t\tfor(var i = 0; i < fields.length; i++) { if (fields[i].checkFn === null || fields[i].checkFn(ticket)) match.push(fields[i]); }\n";
        $js .= "\t\t\treturn asString ? match.map(function(field) { return field.id }).join(',') : match;\n";
        $js .= "\t\t},\n";
        $js .= "\t\tgetFields: function () {\n";
        $js .= "\t\t\treturn fields;\n";
        $js .= "\t\t}\n";
        $js .= "\t};\n";

        $js .= '})()';

        return $js;
    }

    /**
     * @return array
     */
    public function exportToArray()
    {
        $data = [];

        $data['version'] = 1;
        $data['fields']  = [];
        foreach ($this->fields as $f) {
            $data['fields'][] = $f->exportToArray();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function exportToJson()
    {
        return json_encode($this->exportToArray());
    }

    /**
     * @param array $data
     */
    public function importFromArray(array $data)
    {
        foreach ($data['fields'] as $f) {
            $field = new LayoutField($f['field_type'], $f['field_id']);
            $field->setOptionsFromArray($f['options']);
            $this->fields[$field->getId()] = $field;
        }
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->exportToJson();
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = json_decode($data, true);
        $this->importFromArray($data);
    }

    /**
     * @return array
     */
    public function serializeJsonArray()
    {
        return $this->exportToArray();
    }

    /**
     * @param array $data
     *
     * @return Layout
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        $obj->importFromArray($data);

        return $obj;
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getIdsOfFieldType($type)
    {
        $ret = [];
        foreach ($this->fields as $field) {
            if ($type === $field->getFieldType()) {
                $ret[] = $field->getFieldId();
            }
        }

        return $ret;
    }
}
