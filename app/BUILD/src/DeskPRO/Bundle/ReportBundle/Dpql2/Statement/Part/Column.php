<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use Application\DeskPRO\CustomFields\BillingFieldManager;
use Application\DeskPRO\CustomFields\OrganizationFieldManager;
use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\CustomFields\TicketFieldManager;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Func\DpqlFuncRegistry;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\DpqlStatementFactory;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Represents a reference to a column or association.
 */
class Column extends AbstractPart
{
    /**
     * @var DpqlStatementFactory
     */
    private $statementFactory;

    /**
     * @var DpqlFuncRegistry
     */
    private $dpqlFuncRegistry;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TicketFieldManager
     */
    private $ticketFieldManager;

    /**
     * @var BillingFieldManager
     */
    private $billingFieldManager;

    /**
     * @var PersonFieldManager
     */
    private $personFieldManager;

    /**
     * @var OrganizationFieldManager
     */
    private $orgFieldManager;

    /**
     * List of parts in the reference.
     *
     * @var array
     */
    public $parts;

    /**
     * This is used when resolving direct association references to specific columns.
     * Maps a table name to 2 values:
     *  - 0: the unique ID field (usually a number)
     *  - 1: the printable field (name, subject, etc)
     *  - 2: the type of link (if linkable).
     *
     * @var array
     */
    protected static $_tableResolver = [
        'agent_teams'                => ['id', 'name'],
        'article_categories'         => ['id', 'title'],
        'download_categories'        => ['id', 'title'],
        'feedback_categories'        => ['id', 'title'],
        'news_categories'            => ['id', 'title'],
        'brands'                     => ['id', 'name'],
        'custom_field_definition'    => ['id', 'title'],
        'departments'                => ['id', 'title'],
        'feedback_status_categories' => ['id', 'title'],
        'labels_tickets'             => ['label', 'label'],
        'languages'                  => ['id', 'title'],
        'organizations'              => ['id', 'name', 'organization'],
        'people'                     => ['id', '
(CASE
    WHEN (LENGTH(%1$s.first_name) > 0 AND LENGTH(%1$s.last_name) > 0) THEN CONCAT(%1$s.first_name, \' \', %1$s.last_name)
    WHEN LENGTH(%1$s.name) > 0 THEN %1$s.name
    WHEN LENGTH(%1$s.last_name) > 0 THEN %1$s.last_name
    WHEN LENGTH(%1$s.first_name) > 0 THEN %1$s.first_name
    ELSE CONCAT(\'ID-\', %1$s.id)
END)
', 'person'],
        'products'                 => ['id', 'title'],
        'slas'                     => ['id', 'title'],
        'tickets'                  => ['id', 'subject', 'ticket'],
        'ticket_categories'        => ['id', 'title'],
        'ticket_priorities'        => ['id', 'title'],
        'ticket_workflows'         => ['id', 'title'],
        'custom_def_article'       => ['id', 'title'],
        'custom_def_chat'          => ['id', 'title'],
        'custom_def_feedback'      => ['id', 'title'],
        'custom_def_organizations' => ['id', 'title'],
        'custom_def_ticket'        => ['id', 'title'],
        'custom_def_people'        => ['id', 'title'],
    ];

    /**
     * @var array
     */
    protected static $_autoLink = [
        'tickets.id' => ['ticket'],
    ];

    protected static $_conditionResolver = [
        'custom_data_article'       => '%1$s.root_field_id = %2$s',
        'custom_data_feedback'      => '%1$s.root_field_id = %2$s',
        'custom_data_organizations' => '%1$s.root_field_id = %2$s',
        'custom_data_person'        => '%1$s.root_field_id = %2$s',
        'custom_data_ticket'        => '%1$s.root_field_id = %2$s',
        'custom_data_billing'       => '%1$s.root_field_id = %2$s',
        'custom_field_data'         => '%1$s.root_definition_id = %2$s',
        'ticket_slas'               => '%1$s.sla_id = %2$s',
    ];

    /**
     * Constructor.
     *
     * @param DpqlStatementFactory     $statementFactory
     * @param DpqlFuncRegistry         $dpqlFuncRegistry
     * @param EntityManager            $em
     * @param Connection               $connection
     * @param TicketFieldManager       $ticketFieldManager
     * @param BillingFieldManager      $billingFieldManager
     * @param PersonFieldManager       $personFieldManager
     * @param OrganizationFieldManager $orgFieldManager
     * @param array                    $parts
     */
    public function __construct(
        DpqlStatementFactory     $statementFactory,
        DpqlFuncRegistry         $dpqlFuncRegistry,
        EntityManager            $em,
        Connection               $connection,
        TicketFieldManager       $ticketFieldManager,
        BillingFieldManager      $billingFieldManager,
        PersonFieldManager       $personFieldManager,
        OrganizationFieldManager $orgFieldManager,
        array                    $parts
    ) {
        $this->statementFactory    = $statementFactory;
        $this->dpqlFuncRegistry    = $dpqlFuncRegistry;
        $this->em                  = $em;
        $this->connection          = $connection;
        $this->ticketFieldManager  = $ticketFieldManager;
        $this->billingFieldManager = $billingFieldManager;
        $this->personFieldManager  = $personFieldManager;
        $this->orgFieldManager     = $orgFieldManager;
        $this->parts               = $parts;
    }

    /**
     * @param string $name
     *
     * @throws DpqlException
     *
     * @return array
     */
    public static function resolveTable($name)
    {
        if (!array_key_exists($name, self::$_tableResolver)) {
            throw new DpqlException("Missing `{$name}` table resolving information");
        }

        return self::$_tableResolver[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $parts = $this->parts;
        $table = array_shift($parts);

        $from = $statement->getFrom();
        if ($from instanceof Alias) {
            if (strtolower($table) !== strtolower($from->alias)) {
                throw new DpqlException("Alias $table not found.");
            }

            $table = $from->alias;
            if (!$statement->getRepositoryByTable($table)) {
                throw new DpqlException("Unable to resolve table by `$table` alias");
            }
        } elseif (strtolower($table) !== strtolower($statement->getFrom()) && !$statement->isSubQuery()) {
            throw new DpqlException("Invalid table name in column reference (received $table, expected {$statement->getFrom()}).");
        }

        if (!$parts) {
            throw new DpqlException('Missing column/join name in column reference.');
        }

        $sql        = false;
        $printedSql = false;
        $name       = false;
        $renderer   = null;

        end($parts);
        $lastPartKey = key($parts);

        // represents the repository of what we're joining from
        $repository = $statement->getRepositoryByTable($table);
        $sqlTable   = $repository->getTableName();

        $partsSoFar          = [$table];
        $extraConditionValue = false;

        foreach ($parts as $partKey => $part) {
            $partsSoFar[] = $part;
            $partsString  = implode('.', $partsSoFar);

            if (preg_match('/\[(.+)\]$/', $part, $match)) {
                $extraConditionValue = $match[1];
                $part                = substr($part, 0, -strlen($match[0]));
            } else {
                $extraConditionValue = false;
            }

            // are we referencing a field?
            foreach ($repository->getFieldMappings() as $key => $field) {
                if (strtolower($key) == $part) {
                    if (isset($field['dpqlAccess']) && !$field['dpqlAccess']) {
                        throw new DpqlException("$partsString cannot be accessed via DPQL.");
                    }

                    if ($extraConditionValue !== false) {
                        throw new DpqlException("$partsString contains an unexpected extra condition");
                    }

                    $sql = '`'.$sqlTable.'`.`'.$field['columnName'].'`';

                    if ($repository->getTableName() == 'tickets' && $field['columnName'] == 'total_user_waiting') {
                        $sql = "($sql + IF(`$sqlTable`.date_user_waiting AND `$sqlTable`.status = 'awaiting_agent', UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`$sqlTable`.date_user_waiting), 0))";
                    }

                    switch ($field['type']) {
                        case 'datetime':
                            $tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
                            if ($tzOffsetSeconds) {
                                $sql = "($sql + INTERVAL $tzOffsetSeconds SECOND)";
                            }

                            $renderer = 'datetime';
                            break;

                        case 'integer':
                        case 'smallint':
                        case 'bigint':
                        case 'decimal':
                        case 'float':
                            $renderer = 'number';
                            break;

                        case 'date':
                            $renderer = 'date';
                            break;

                        case 'time':
                            $renderer = 'time';
                            break;

                        case 'boolean':
                            $renderer = 'boolean';
                            break;

                        case 'string':
                        case 'text':
                            $renderer = 'string';
                            break;
                    }

                    if ($renderer == 'number' && !empty($field['id'])) {
                        $renderer = 'id';
                    }

                    $linkLookup = $repository->getTableName().'.'.$part;

                    if (isset(self::$_autoLink[$linkLookup])) {
                        $lookup = self::$_autoLink[$linkLookup];

                        if ($section == 'split') {
                            $argSelect = [$statement->getSplitSql()->addSelectField($sql)];
                        } else {
                            $argSelect = [$select->addSelectField($sql)];
                        }

                        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer, ResultMetadata $metadata) use ($lookup, $argSelect) {
                            return $this->dpqlFuncRegistry->getLinkFunction()->formatLink($value, $lookup[0], $argSelect, $row, $valueRenderer, $renderer, $metadata);
                        };
                    }

                    $name = $part;
                    break 2; // break $parts loop
                }
            }

            foreach ($repository->getAssociationMappings() as $association) {
                if (empty($association['joinColumns'])) {
                    // need to know how to make the join; ignore this
                    continue;
                }

                foreach ($association['joinColumns'] as $joinColumn) {
                    // are we referencing a field that is only listed in an association?
                    if (strtolower($joinColumn['name']) == $part) {
                        if ($extraConditionValue !== false) {
                            throw new DpqlException("$partsString contains an unexpected extra condition");
                        }

                        $sql  = '`'.$sqlTable.'`.`'.$joinColumn['name'].'`';
                        $name = $part;
                        break 3; // break $parts loop
                    }
                }
            }

            foreach ($repository->getReportAssociations() as $name => $association) {
                if (strtolower($name) == $part) {
                    $target          = $association['targetEntity'];
                    $childRepository = $this->em->getRepository($target);

                    if (!($childRepository instanceof AbstractEntityRepository)) {
                        throw new DpqlException("$partsString cannot be accessed via DPQL.");
                    }

                    $childSqlTable = $childRepository->getTableName();
                    $joinAlias     = "{$sqlTable}_{$name}";
                    if ($extraConditionValue !== false) {
                        if (!isset(self::$_conditionResolver[$childSqlTable])) {
                            throw new DpqlException("$partsString contains an unexpected extra condition");
                        }

                        $joinAlias .= '_'.preg_replace('/[^a-zA-Z0-9_]/', '_', $extraConditionValue);
                    }

                    $joinConditions[] = sprintf($association['conditions'], $joinAlias, $sqlTable);
                    if ($extraConditionValue !== false) {
                        $joinConditions[] = sprintf(
                            self::$_conditionResolver[$childSqlTable], $joinAlias, $this->connection->quote($extraConditionValue)
                        );
                    }

                    $select->addJoin(
                        "$joinAlias",
                        "LEFT JOIN `$childSqlTable` AS `$joinAlias` ON (".implode(' AND ', $joinConditions).')'
                    );

                    $repository = $childRepository; // now references come from this table
                    $sqlTable   = $joinAlias;

                    continue 2; // continue $parts loop
                }
            }

            foreach ($repository->getAssociationMappings() as $association) {
                // are we referencing an association?
                if (strtolower($association['fieldName']) == $part) {
                    $target          = $association['targetEntity'];
                    $childRepository = $this->em->getRepository($target);

                    if ($statement->isSubQuery() && $childRepository === $statement->getFromEntityRepository()) {
                        $childRepository = $repository;
                    }

                    if ((isset($association['dpqlAccess']) && !$association['dpqlAccess'])
                        || !($childRepository instanceof AbstractEntityRepository)
                        || $association['type'] == ClassMetadataInfo::MANY_TO_MANY
                    ) {
                        throw new DpqlException("$partsString cannot be accessed via DPQL.");
                    }

                    $childSqlTable = $childRepository->getTableName();
                    $joinAlias     = "{$sqlTable}_{$association['fieldName']}";

                    if ($extraConditionValue !== false) {
                        if (!isset(self::$_conditionResolver[$childSqlTable])) {
                            throw new DpqlException("$partsString contains an unexpected extra condition");
                        }

                        $joinAlias .= '_'.preg_replace('/[^a-zA-Z0-9_]/', '_', $extraConditionValue);
                    }

                    if (!empty($association['joinColumns'])) {
                        // join can be resolved directly
                        $joinColumns = $association['joinColumns'];
                        $sourceTable = $sqlTable;
                        $joinTable   = $joinAlias;
                    } else {
                        $childAssociations = $childRepository->getAssociationMappings();
                        if (!empty($childAssociations[$association['mappedBy']]['joinColumns'])) {
                            // join details are on the other table
                            $joinColumns = $childAssociations[$association['mappedBy']]['joinColumns'];
                            $sourceTable = $joinAlias;
                            $joinTable   = $sqlTable;
                        } else {
                            $joinColumns = [];
                        }
                    }

                    if (!$joinColumns) {
                        throw new DpqlException("$partsString cannot be accessed via DPQL.");
                    }

                    $joinConditions = [];
                    foreach ($joinColumns as $joinColumn) {
                        $joinConditions[] =
                            "`$sourceTable`.`$joinColumn[name]` = "
                            ."`$joinTable`.`$joinColumn[referencedColumnName]`";
                    }

                    if ($extraConditionValue !== false) {
                        $joinConditions[] = sprintf(
                            self::$_conditionResolver[$childSqlTable], $joinAlias, $this->connection->quote($extraConditionValue)
                        );
                    }

                    $select->addJoin(
                        "$joinAlias",
                        "LEFT JOIN `$childSqlTable` AS `$joinAlias` ON (".implode(' AND ', $joinConditions).')'
                    );

                    $repository = $childRepository; // now references come from this table
                    $sqlTable   = $joinAlias;

                    continue 2; // continue $parts loop
                }
            }

            throw new DpqlException("Unknown column reference $partsString");
        }

        if ($partKey !== $lastPartKey) {
            throw new DpqlException('Did not get to end of column references');
        }

        if ($sql === false) {
            $assocTable = $repository->getTableName();
            $name       = $part;
            if ($assocTable == 'ticket_slas') {
                $call    = $this->statementFactory->createColumn(array_merge($this->parts, ['sla']));
                $prepped = $call->prepare($statement, $section, $stack, $select, $result);

                return new Prepared($prepped->sql(), $this->_prettifyColumnName($name), $prepped->printed());
            } elseif ($assocTable === 'custom_field_data') {
                $call = $this->statementFactory->createFunctionCall('if', [
                    $this->statementFactory->createColumn(array_merge($this->parts, ['id'])),
                    $this->statementFactory->createColumn(array_merge($this->parts, ['definition', 'title'])),
                    $this->statementFactory->createColumn(array_merge($this->parts, ['input'])),
                ]);
                $prepped = $call->prepare($statement, $section, $stack, $select, $result);

                return new Prepared($prepped->sql(), $this->_prettifyColumnName($name), false, $renderer);
            } elseif (preg_match('/^custom_data_/', $assocTable)) {
                $custom_def_table = str_replace('_data_', '_def_', $assocTable);
                switch ($custom_def_table) {
                    case 'custom_def_ticket':
                        $manager = $this->ticketFieldManager;
                        break;
                    case 'custom_def_billing':
                        $manager = $this->billingFieldManager;
                        break;
                    case 'custom_def_people':
                        $manager = $this->personFieldManager;
                        break;
                    case 'custom_def_organizations':
                        $manager = $this->orgFieldManager;
                        break;
                    default:
                        $manager = null;
                        break;
                }

                $field = null;
                if ($manager) {
                    $field = $manager->getFieldFromId($extraConditionValue);
                }

                $renderer = null;
                if ($field && (array_search($type = $field->getTypeName(), ['date', 'datetime']) !== false)) {
                    $call    = $this->statementFactory->createColumn(array_merge($this->parts, ['value']));
                    $prepped = $call->prepare($statement, $section, $stack, $select, $result);

                    $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer, ResultMetadata $metadata) use ($type) {
                        $date = $value ? new \DateTime('@'.$value) : null;

                        return $valueRenderer->renderValue($date ?: null, $type, $metadata);
                    };
                } else {
                    $call = $this->statementFactory->createFunctionCall('if', [
                        $this->statementFactory->createColumn(array_merge($this->parts, ['value'])),
                        $this->statementFactory->createColumn(array_merge($this->parts, ['field', 'title'])),
                        $this->statementFactory->createColumn(array_merge($this->parts, ['input'])),
                    ]);
                    $prepped = $call->prepare($statement, $section, $stack, $select, $result);
                }

                return new Prepared($prepped->sql(), $this->_prettifyColumnName($field ? $field->getTitle() : $name), false, $renderer);
            } elseif (preg_match('/^custom_def_/', $assocTable)) {
                $call = $this->statementFactory->createFunctionCall('if', [
                    $this->statementFactory->createColumn(array_merge($this->parts, ['parent', 'id'])),
                    $this->statementFactory->createColumn(array_merge($this->parts, ['parent', 'title'])),
                    $this->statementFactory->createColumn(array_merge($this->parts, ['title'])),
                ]);
                $prepped = $call->prepare($statement, $section, $stack, $select, $result);

                return new Prepared($prepped->sql(), $this->_prettifyColumnName($name));
            } elseif (isset(self::$_tableResolver[$assocTable])) {
                $resolver = self::$_tableResolver[$assocTable];

                if ($section === 'where') {
                    $sql = strpos($resolver[1], '%1$s') !== false
                        ? sprintf($resolver[1], $sqlTable)
                        : "`$sqlTable`.`$resolver[1]`";
                } elseif ($stack || in_array($section, ['order'])) {
                    // if we have a parent of any sort, act on the printed value
                    $sql = strpos($resolver[1], '%1$s') !== false
                        ? sprintf($resolver[1], $sqlTable)
                        : "`$sqlTable`.`$resolver[1]`";
                } else {
                    $sql = "`$sqlTable`.`$resolver[0]`";
                }

                $printedSql = strpos($resolver[1], '%1$s') !== false ? sprintf($resolver[1], $sqlTable) : "`$sqlTable`.`$resolver[1]`";

                if (isset($resolver[2])) {
                    if ($section == 'split') {
                        $argSelect = [$statement->getSplitSql()->addSelectField("`$sqlTable`.`$resolver[0]`")];
                    } else {
                        $argSelect = [$select->addSelectField("`$sqlTable`.`$resolver[0]`")];
                    }

                    $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer, ResultMetadata $metadata) use ($resolver, $argSelect) {
                        return $this->dpqlFuncRegistry->getLinkFunction()->formatLink($value, $resolver[2], $argSelect, $row, $valueRenderer, $renderer, $metadata);
                    };
                }
            } else {
                throw new DpqlException("$partsString cannot be referenced directly. Please reference a specific column.");
            }
        }

        return new Prepared($sql, $this->_prettifyColumnName($name), $printedSql, $renderer);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return implode('.', $this->parts);
    }

    /**
     * Turns a column reference (such as ticket_id) into a nicer looking,
     * printable version (Ticket ID).
     *
     * @param string $name
     *
     * @return string
     */
    protected function _prettifyColumnName($name)
    {
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace('Id', 'ID', $name);

        return $name;
    }
}
