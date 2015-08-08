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

namespace Application\ImportBundle\Generator\Exporter\Helper;

use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Application\ImportBundle\Generator\Exporter\Parser\NotArrayException;

/**
 * Class ColumnHelper
 * @package Application\ImportBundle\Generator\Exporter\Helper
 */
class ColumnHelper
{
    /**
     * Check if a record has all required columns
     *
     * @param array|null $record
     * @param array      $columns
     * @param boolean    $throw_exception
     *
     * @return bool
     * @throws NoColumnException
     */
    public static function hasRequiredColumns(array $record = null, array $columns, $throw_exception = true)
    {
        if ( ! is_array($record)) {
            throw new NoColumnException('Record is not array');
        }

        foreach ($columns as $column) {
            if (array_key_exists($column, $record) === false) {
                if ($throw_exception) {
                    throw new NoColumnException(sprintf(
                        'Column `%s` not found, exists [%s]',
                        $column,  implode(', ', array_keys($record))
                    ));
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Check if a record has any of required columns
     *
     * @param array|null $record
     * @param array      $columns
     * @param boolean    $throw_exception
     *
     * @return bool
     * @throws NoColumnException
     */
    public static function hasAnyRequiredColumn(array $record = null, array $columns, $throw_exception = true)
    {
        if ( ! is_array($record)) {
            throw new NoColumnException('Record is not array');
        }

        foreach ($columns as $column) {
            if (array_key_exists($column, $record) === true) {
                return true;
            }
        }

        if ($throw_exception) {
            throw new NoColumnException(sprintf(
                'Columns `%s` not found, exists [%s]',
                implode(', ', $columns), implode(', ', array_keys($record))
            ));
        }

        return false;
    }

    /**
     * Check if a record column is array
     *
     * @param array|null $record
     * @param string     $column
     * @param bool       $throw_exception
     *
     * @return bool
     * @throws NotArrayException
     */
    public static function isArrayColumn(array $record = null, $column, $throw_exception = true)
    {
        if ( ! is_array($record)) {
            throw new NotArrayException(sprintf('Column `%s` is not array, record is not array', $column));
        }

        self::hasRequiredColumns($record, array($column), $throw_exception);

        if (is_array($record[$column]) === false) {
            if ($throw_exception) {
                throw new NotArrayException(sprintf('Column `%s` is not array', $column));
            }

            return false;
        }

        return true;
    }
}