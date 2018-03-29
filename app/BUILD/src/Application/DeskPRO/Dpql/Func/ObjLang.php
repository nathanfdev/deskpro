<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Renderer\AbstractRenderer;
use Application\DeskPRO\Dpql\Renderer\Values\AbstractValues;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

/**
 * Handler for OBJ_LANG function.
 *
 * OBJ_LANG(field, typename, prop = "title")
 *
 * Example: OBJ_LANG(ticket_object_use_logs.snippet.category.id, 'text_snippet_categories')
 * Example: OBJ_LANG(ticket_object_use_logs.snippet.id, 'text_snippet')
 */
class ObjLang extends AbstractFunc
{
    /**
     * Prepares the function for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Display             $statement
     * @param string                                                  $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $result);

        if (count($this->_arguments) < 2) {
            throw new Exception('OBJ_LANG() Must have at least a field (arg1) and a type (arg2).');
        }

        $sql = $prepped->sql();

        $ref_type = $this->_toLiteral($this->_arguments[1]);
        $ref_prop = !empty($this->_arguments[2]) ? $this->_toLiteral($this->_arguments[2]) : 'title';
        $lang_id  = App::getCurrentPerson() ? App::getCurrentPerson()->getLanguage() : 0;

        $renderer = function (AbstractValues $valueRenderer, $value, array $row, AbstractRenderer $renderer) use ($ref_type, $ref_prop, $lang_id) {
            if ($value === null) {
                return $valueRenderer->renderValue(null, 'string');
            }

            $value = $valueRenderer->renderValue($value, 'id');

            $db   = App::getDb();
            $text = $db->fetchColumn('
                SELECT value
                FROM object_lang
                WHERE ref = ? AND prop_name = ? AND language_id = ?
            ', [$ref_type.".$value", $ref_prop, $lang_id]);

            if (!$text) {
                $text = $db->fetchColumn('
                    SELECT value
                    FROM object_lang
                    WHERE ref = ? AND prop_name = ? AND language_id != ? AND value != ""
                    LIMIT 1
                ', [$ref_type.".$value", $ref_prop, $lang_id]);
            }

            if (!$text) {
                $text = "[$ref_type] $value";
            }

            return $text;
        };

        return new Prepared($sql, 'OBJ_LANG('.$prepped->name().')', false, $renderer);
    }

    protected function _toLiteral(\Application\DeskPRO\Dpql\Statement\Part\AbstractPart $part)
    {
        if ($part instanceof \Application\DeskPRO\Dpql\Statement\Part\StringPart) {
            return $part->string;
        } elseif ($part instanceof \Application\DeskPRO\Dpql\Statement\Part\Number) {
            return $part->number;
        } else {
            throw new DpqlException('Only literal values may be used for '.$this->_name.'() parameters.');
        }
    }
}
