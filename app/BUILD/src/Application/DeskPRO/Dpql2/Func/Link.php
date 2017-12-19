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
 * Links to the specified content if possible (based on output type).
 */
class Link extends AbstractFunc
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
        if (count($this->_arguments) < 2) {
            throw new Exception('LINK() requires at least 2 arguments.');
        }

        $arguments     = $this->_arguments;
        $print         = array_shift($arguments);
        $format        = array_shift($arguments);
        $formatLiteral = $this->_toLiteral($format);

        $argNames  = [];
        $argSelect = [];
        foreach ($arguments as $argument) {
            $prepped     = $argument->prepare($statement, $section, $stack, $select, $result);
            $argNames[]  = $prepped->name();
            $argSelect[] = $select->addSelectField($prepped->printed());
        }

        $preppedPrint = $print->prepare($statement, $section, $stack, $select, $result);

        $renderer = function (AbstractValues $valueRenderer, $value, array $row, AbstractRenderer $renderer) use ($formatLiteral, $argSelect) {
            return Link::formatLink($value, $formatLiteral, $argSelect, $row, $valueRenderer, $renderer);
        };

        return new Prepared($preppedPrint->sql(), $preppedPrint->name(), false, $renderer);
    }

    public static function formatLink($print, $format, array $argSelect, array $row,
        AbstractValues $valueRenderer, AbstractRenderer $renderer
    ) {
        $breakEarly = (
            $print === null
                || !($valueRenderer instanceof \Application\DeskPRO\Dpql2\Renderer\Values\Html)
        );

        $print = array_key_exists('hierarchy_title', $row)
               ? $row['hierarchy_title']
               : $valueRenderer->renderValue($print, 'string');

        if ($breakEarly) {
            return $print;
        }

        switch ($format) {
            case 'ticket': $format       = 'agent/#app.tickets,t:%d'; break;
            case 'person': $format       = 'agent/#app.people,p:%d'; break;
            case 'organization': $format = 'agent/#app.people,o:%d'; break;
        }

        $argValues = [];
        foreach ($argSelect as $key) {
            $argValues[] = urlencode($renderer->getColumnValue($row, $key));
        }

        $link = App::getRequest()->getUriForPath('').'/'.vsprintf($format, $argValues);

        return '<a href="'.htmlspecialchars($link).'" target="_blank">'.$print.'</a>';
    }
}
