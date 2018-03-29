<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Orb\Util\Strings;

/**
 * Abstract base for all placeholder (%NAME%) references.
 */
abstract class AbstractPlaceholder implements DpqlPlaceholderInterface
{
    /**
     * @var DpqlContextStorage
     */
    protected $dpqlContextStorage;

    /**
     * Constructor.
     *
     * @param DpqlContextStorage $dpqlContextStorage
     */
    public function __construct(DpqlContextStorage $dpqlContextStorage)
    {
        $this->dpqlContextStorage = $dpqlContextStorage;
    }

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
     * {@inheritdoc}
     */
    public function prepareWithIntervals(SqlSelect $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result, array $intervals = [])
    {
        return $this->prepare($statement, $section, $stack, $select, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, SelectPart $statement, $section, array $stack,
        SqlSelect $select, ResultMetadata $result, array $intervals = []
    ) {
        return false;
    }

    /**
     * Gets the placeholder in DPQL.
     *
     * @return string
     */
    protected function _toDpql()
    {
        $reflection = new \ReflectionClass($this);

        $name = $reflection->getShortName();
        $name = Strings::camelCaseToUnderscore($name);
        $name = strtoupper($name);

        return '%'.$name.'%';
    }

    /**
     * @return Person|null
     */
    protected function getPerson()
    {
        $context = $this->dpqlContextStorage->getContext();
        if (!$context) {
            return;
        }

        return $context->getPerson();
    }
}
