<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\StringPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Orb\Util\Arrays;

/**
 * Class JsonExtract.
 */
class DpqlJsonExtract extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) !== 2) {
            throw new DpqlException('DPQL_JSON_EXTRACT() can only accept at least 2 arguments.');
        }
        if ($section !== 'select') {
            throw new DpqlException('DPQL_JSON_EXTRACT() may only be used in SELECT.');
        }

        /** @var AbstractPart $expression */
        $expression = array_shift($arguments);
        /** @var StringPart $patterns */
        $pattern = array_shift($arguments);

        $prepped  = $expression->prepare($statement, $section, $stack, $select, $metadata);
        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer) use ($pattern) {
            $decoded = json_decode($value, true);
            if (!$decoded) {
                return 'NULL';
            }

            $extracted = $this->getValueByPropertyPath($decoded, Arrays::removeEmptyString(preg_split('/[\.\[\]]+/', $pattern->string)));
            if (!$extracted) {
                return 'NULL';
            }
            if (is_array($extracted)) {
                return json_encode($extracted);
            }

            return $extracted;
        };

        return new Prepared($prepped->sql(), 'DPQL_JSON_EXTRACT('.$prepped->sql().')', false, $renderer);
    }

    /**
     * @param array $decoded
     * @param array $propertyPath
     *
     * @return mixed
     */
    private function getValueByPropertyPath(array $decoded, array $propertyPath)
    {
        $rootPath = array_shift($propertyPath);
        if ($rootPath === '$') {
            return $this->getValueByPropertyPath($decoded, $propertyPath);
        }

        if (isset($decoded[$rootPath])) {
            if (!$propertyPath) {
                return $decoded[$rootPath];
            } else {
                return $this->getValueByPropertyPath($decoded[$rootPath], $propertyPath);
            }
        }

        return;
    }
}
