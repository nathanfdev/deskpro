<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser;

use Application\ImportBundle\Generator\AbstractGenerator;
use DateTime;

/**
 * Abstract generator exporter parser
 *
 * Class AbstractParser
 * @package Application\ImportBundle\Generator\Exporter\Parser
 */
abstract class AbstractParser extends AbstractGenerator implements ParserInterface
{
    /**
     * Check if a record has all required columns
     *
     * @param array   $record
     * @param array   $columns
     * @param boolean $throw_exception
     *
     * @return bool
     * @throws NoColumnException
     */
    protected function hasRequiredColumns(array $record, array $columns, $throw_exception = true)
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $record) === false) {
                if ($throw_exception) {
                    throw new NoColumnException(sprintf('Column `%s` not found', $column));
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Check if a record column is array
     *
     * @param array  $record
     * @param string $column
     * @param bool   $throw_exception
     *
     * @return bool
     *
     * @throws NoColumnException
     * @throws NotArrayException
     */
    protected function isArrayColumn(array $record, $column, $throw_exception = true)
    {
        if (array_key_exists($column, $record) === false) {
            if ($throw_exception) {
                throw new NoColumnException(sprintf('Column `%s` not found', $column));
            }

            return false;
        }

        if (is_array($record[$column]) === false) {
            if ($throw_exception) {
                throw new NotArrayException(sprintf('Column `%s` is not array', $column));
            }

            return false;
        }

        return true;
    }

    /**
     * Returns date time object from string or current date time if the format is empty
     *
     * @param string $format
     * @return DateTime
     */
    protected function getFromStringOrCurrentDateTime($format)
    {
        return $format ? new DateTime($format) : new DateTime();
    }

    /**
     * Returns true if value is "true" or intval of value = 1
     *
     * @param int|string $value
     * @return bool
     */
    protected function isBooleanTrue($value)
    {
        return $value === 'true' || (int)$value === 1;
    }
}
