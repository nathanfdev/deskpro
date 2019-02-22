<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Formats output using the given type and options.
 */
class DpqlFormat extends AbstractDpqlFunc
{
    /**
     * @var DpqlContextStorage
     */
    private $contextStorage;

    /**
     * Constructor.
     *
     * @param DpqlContextStorage $contextStorage
     */
    public function __construct(DpqlContextStorage $contextStorage)
    {
        $this->contextStorage = $contextStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) < 2) {
            throw new DpqlException('DPQL_FORMAT() requires at least 2 arguments.');
        }

        $value       = array_shift($arguments);
        $type        = array_shift($arguments);
        $typeLiteral = $this->_toLiteral($type);

        $argNames    = [];
        $argLiterals = [];
        foreach ($arguments as $argument) {
            $prepped       = $argument->prepare($statement, $section, $stack, $select, $metadata);
            $argNames[]    = $prepped->name();
            $argLiterals[] = $this->_toLiteral($argument);
        }

        $preppedValue = $value->prepare($statement, $section, $stack, $select, $metadata);
        $preppedType  = $type->prepare($statement, $section, $stack, $select, $metadata);

        if ($argNames) {
            $argNameOutput = ', '.implode(', ', $argNames);
        } else {
            $argNameOutput = '';
        }

        $name = 'DPQL_FORMAT('.$preppedValue->name().', '.$preppedType->name().$argNameOutput.')';

        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer, ResultMetadata $metadata) use ($typeLiteral, $argLiterals) {
            if ($value === null) {
                return $valueRenderer->renderValue(null, 'string', $metadata);
            }

            switch (strtolower($typeLiteral)) {
                case 'number':
                    if ($argLiterals) {
                        return $valueRenderer->escapeValue(number_format((float) $value, $argLiterals[0]));
                    }
                    break;

                case 'date':
                    if ($argLiterals) {
                        $context = $this->contextStorage->getContext();
                        $tz      = $context ? $context->getTimezone() : 'UTC';

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

                    return $valueRenderer->escapeValue(number_format((float) $value * 100, $decimals).'%');
            }

            return $valueRenderer->renderValue($value, $typeLiteral, $metadata);
        };

        return new Prepared($preppedValue->sql(), $name, false, $renderer);
    }
}
