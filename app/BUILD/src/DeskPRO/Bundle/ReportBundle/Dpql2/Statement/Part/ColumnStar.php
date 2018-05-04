<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use Application\DeskPRO\Entity\CustomDefArticle;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\DpqlStatementFactory;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Represents a reference to all data in a table.
 */
class ColumnStar extends AbstractPart
{
    /**
     * @var DpqlStatementFactory
     */
    private $statementFactory;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * List of parts in the reference.
     *
     * @var array
     */
    public $parts;

    /**
     * Constructor.
     *
     * @param DpqlStatementFactory $statementFactory
     * @param EntityManager        $em
     * @param array                $parts
     */
    public function __construct(DpqlStatementFactory $statementFactory, EntityManager $em, array $parts)
    {
        $this->statementFactory = $statementFactory;
        $this->em               = $em;

        array_pop($parts); // pop off the ".*"
        $this->parts = $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $parts = $this->parts;
        $table = array_shift($parts);

        if (strtolower($table) != strtolower($statement->getFrom())) {
            throw new DpqlException("Invalid table name in column reference (received $table, expected {$statement->getFrom()}).");
        }

        end($parts);
        $lastPartKey = key($parts);

        // represents the repository of what we're joining from
        $repository = $statement->getFromEntityRepository();

        $partsSoFar = [$table];

        foreach ($parts as $partKey => $part) {
            $partsSoFar[] = $part;
            $partsString  = implode('.', $partsSoFar);

            if (preg_match('/\[(.+)\]$/', $part, $match)) {
                $part = substr($part, 0, -strlen($match[0]));
            }

            // are we referencing a field?
            foreach ($repository->getFieldMappings() as $key => $field) {
                if (strtolower($key) == $part) {
                    throw new DpqlException("Select star conditions may only include references to tables or associations (did not expect $partsString).");
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
                        throw new DpqlException("Select star conditions may only include references to tables or associations (did not expect $partsString).");
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

                    $repository = $childRepository;
                    continue 2; // continue $parts loop
                }
            }

            foreach ($repository->getAssociationMappings() as $association) {
                // are we referencing an association?
                if (strtolower($association['fieldName']) == $part) {
                    $target          = $association['targetEntity'];
                    $childRepository = $this->em->getRepository($target);

                    if ((isset($association['dpqlAccess']) && !$association['dpqlAccess'])
                        || !($childRepository instanceof AbstractEntityRepository)
                        || $association['type'] == ClassMetadataInfo::MANY_TO_MANY
                    ) {
                        throw new DpqlException("$partsString cannot be accessed via DPQL.");
                    }

                    if (!empty($association['joinColumns'])) {
                        // join can be resolved directly
                        $joinColumns = $association['joinColumns'];
                    } else {
                        $childAssociations = $childRepository->getAssociationMappings();
                        if (!empty($childAssociations[$association['mappedBy']]['joinColumns'])) {
                            // join details are on the other table
                            $joinColumns = $childAssociations[$association['mappedBy']]['joinColumns'];
                        } else {
                            $joinColumns = [];
                        }
                    }

                    if (!$joinColumns) {
                        throw new DpqlException("$partsString cannot be accessed via DPQL.");
                    }

                    $repository = $childRepository; // now references come from this table
                    continue 2; // continue $parts loop
                }
            }

            throw new DpqlException("Unknown column reference $partsString");
        }

        if ($parts && $partKey !== $lastPartKey) {
            throw new DpqlException('Did not get to end of column references');
        }

        foreach ($repository->getFieldMappings() as $key => $field) {
            if (isset($field['dpqlAccess']) && !$field['dpqlAccess']) {
                continue;
            }

            $column = $this->statementFactory->createColumn(array_merge($partsSoFar, [$key]));
            $statement->addPreparedSelectField($column->prepare($statement, $section, $stack, $select, $metadata));
        }
        foreach ($repository->getAssociationMappings() as $association) {
            if ((isset($association['dpqlAccess']) && !$association['dpqlAccess'])
                || $association['type'] == ClassMetadataInfo::MANY_TO_MANY
            ) {
                continue;
            }

            if ($association['type'] & ClassMetadataInfo::TO_ONE) {
                try {
                    $column = $this->statementFactory->createColumn(array_merge($partsSoFar, [$association['fieldName']]));
                    $statement->addPreparedSelectField($column->prepare($statement, $section, $stack, $select, $metadata));
                } catch (DpqlException $e) {
                }
            } elseif (preg_match('/CustomData([a-zA-Z]+)$/', $association['targetEntity'], $match)) {
                switch ($match[1]) {
                    case 'Article':
                        $type = CustomDefArticle::class;
                        break;
                    case 'Feedback':
                        $type = CustomDefFeedback::class;
                        break;
                    case 'Organization':
                        $type = CustomDefOrganization::class;
                        break;
                    case 'Person':
                        $type = CustomDefPerson::class;
                        break;
                    case 'Ticket':
                        $type = CustomDefTicket::class;
                        break;
                    default:
                        $type = '';
                        break;
                }

                if ($type) {
                    foreach ($this->em->getRepository($type)->getTopFields() as $field) {
                        $column = $this->statementFactory->createColumn(array_merge($partsSoFar, [$association['fieldName']."[$field->id]"]));
                        $statement->addPreparedSelectField(
                            $column->prepare($statement, $section, $stack, $select, $metadata), $field->title
                        );
                    }
                }
            }
        }

        return new Prepared(false);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return implode('.', $this->parts).'.*';
    }
}
