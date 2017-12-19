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
 * Formats output using the given type and options.
 */
class Format extends AbstractFunc
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
            throw new Exception('FORMAT() requires at least 2 arguments.');
        }

        $arguments   = $this->_arguments;
        $value       = array_shift($arguments);
        $type        = array_shift($arguments);
        $typeLiteral = $this->_toLiteral($type);

        $argNames    = [];
        $argLiterals = [];
        foreach ($arguments as $argument) {
            $prepped       = $argument->prepare($statement, $section, $stack, $select, $result);
            $argNames[]    = $prepped->name();
            $argLiterals[] = $this->_toLiteral($argument);
        }

        $preppedValue = $value->prepare($statement, $section, $stack, $select, $result);
        $preppedType  = $type->prepare($statement, $section, $stack, $select, $result);

        if ($argNames) {
            $argNameOutput = ', '.implode(', ', $argNames);
        } else {
            $argNameOutput = '';
        }

        $name = 'FORMAT('.$preppedValue->name().', '.$preppedType->name().$argNameOutput.')';

        $renderer = function (AbstractValues $valueRenderer, $value, array $row, AbstractRenderer $renderer) use ($typeLiteral, $argLiterals) {
            if ($value === null) {
                return $valueRenderer->renderValue(null, 'string');
            }

            switch (strtolower($typeLiteral)) {
                case 'number':
                    if ($argLiterals) {
                        return $valueRenderer->escapeValue(number_format($value, $argLiterals[0]));
                    }
                    break;

                case 'date':
                    if ($argLiterals) {
                        $tz = App::getCurrentPerson()->getTimezone();
                        try {
                            $date = new \DateTime($value, new \DateTimeZone($tz));

                            return $valueRenderer->escapeValue($date->format($argLiterals[0]));
                        } catch (\Exception $e) {
                            return $valueRenderer->escapeValue($value);
                        }
                    }
                    break;

                case 'percent':
                    $decimals = isset($argLiterals[0]) ? $argLiterals[0] : 2;

                    return $valueRenderer->escapeValue(number_format($value * 100, $decimals).'%');
            }

            return $valueRenderer->renderValue($value, $typeLiteral);
        };

        return new Prepared($preppedValue->sql(), $name, false, $renderer);
    }
}
