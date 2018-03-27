<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException as DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Number;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\StringPart;
use Orb\Util\Strings;

/**
 * Abstract base for all DPQL function calls.
 */
abstract class AbstractDpqlFunc implements DpqlFunctionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getName()
    {
        $reflection = new \ReflectionClass(static::class);

        $func = Strings::camelCaseToUnderscore($reflection->getShortName());
        $func = strtoupper($func);

        return $func;
    }

    /**
     * Gets a literal value for the specified part.
     *
     * @param AbstractPart $part
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     *
     * @return mixed
     */
    protected function _toLiteral(AbstractPart $part)
    {
        if ($part instanceof StringPart) {
            return $part->string;
        } elseif ($part instanceof Number) {
            return $part->number;
        } else {
            throw new DpqlException('Only literal values may be used for DPQL func parameters.');
        }
    }
}
