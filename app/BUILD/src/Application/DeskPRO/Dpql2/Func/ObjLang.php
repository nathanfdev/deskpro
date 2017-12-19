<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql2\Func;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql2\Exception;
use Application\DeskPRO\Dpql2\Renderer\AbstractRenderer;
use Application\DeskPRO\Dpql2\Renderer\Values\AbstractValues;
use Application\DeskPRO\Dpql2\Statement\Part\Prepared;
use Application\DeskPRO\Dpql2\Statement\SelectPart;

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
     * @param \Application\DeskPRO\Dpql2\Statement\SelectPart          $statement
     * @param string                                                   $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql2\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql2\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql2\Exception
     *
     * @return \Application\DeskPRO\Dpql2\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(
        SelectPart $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
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

    protected function _toLiteral(\Application\DeskPRO\Dpql2\Statement\Part\AbstractPart $part)
    {
        if ($part instanceof \Application\DeskPRO\Dpql2\Statement\Part\StringPart) {
            return $part->string;
        } elseif ($part instanceof \Application\DeskPRO\Dpql2\Statement\Part\Number) {
            return $part->number;
        } else {
            throw new DpqlException('Only literal values may be used for '.$this->_name.'() parameters.');
        }
    }
}
