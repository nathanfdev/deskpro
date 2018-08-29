<?php

namespace Application\DeskPRO\People\PersonMerge\Backup;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

/**
 * Most of the data we put to db directly.
 */
class PersonRestore
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Person $person
     * @param array  $data
     *
     * @return bool
     */
    public function restore(Person $person, $data)
    {
        if (!$person->getId()) {
            $this->em->persist($person);
            $this->em->flush($person);
        }

        $this->restoreSimpleFields($person, $data);
        $this->restoreCustomData($person, $data);
        $this->restoreGroups($person, $data);
        $this->restoreBrands($person, $data);

        $this->em->refresh($person);
        if ($person->isAgent()) {
            $this->restoreAgentTeams($person, $data);
        }

        $this->restoreRelatedTables($person, $data);

        // We did a lot of raw sql updates
        // refresh entity to prevent possible overwrite
        $this->em->refresh($person);

        return true;
    }

    /**
     * @param Person $person
     * @param array  $data
     */
    protected function restoreSimpleFields(Person $person, $data)
    {
        if (!isset($data['simple_fields']) || empty($data['simple_fields'])) {
            return;
        }

        $fields = array_keys($data['simple_fields']);
        $values = array_values($data['simple_fields']);
        array_walk($values, function (&$item, $key) {
            if (is_null($item)) {
                $item = 'null';
            } elseif (is_string($item) && !is_numeric($item)) {
                $item = '\''.$item.'\'';
            }
        });

        $setParts = [];
        foreach ($fields as $key => $field) {
            $setParts[] = sprintf('%s = %s', $field, $values[$key]);
        }

        $this->em->getConnection()->executeUpdate(
            sprintf(
                'UPDATE IGNORE people SET %s WHERE id = :person_id',
                implode(', ', $setParts)
            ),
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );
    }

    /**
     * @param Person $person
     * @param array  $data
     */
    protected function restoreCustomData(Person $person, $data)
    {
        if (!array_key_exists('custom_data', $data)) {
            return;
        }

        $this->em->getConnection()->executeUpdate(
            'DELETE FROM custom_data_person WHERE person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        if (empty($data['custom_data'])) {
            return;
        }

        foreach ($data['custom_data'] as $customData) {
            $this->tableInsert('custom_data_person', [
                'person_id'     => $person->getId(),
                'field_id'      => $customData['field_id'],
                'root_field_id' => $customData['root_field_id'],
                'value'         => $customData['value'],
                'input'         => $customData['input'],
            ]);
        }
    }

    /**
     * @param Person $person
     * @param array  $data
     */
    protected function restoreGroups(Person $person, $data)
    {
        if (!array_key_exists('groups', $data)) {
            return;
        }

        $this->em->getConnection()->executeUpdate(
            'DELETE FROM person2usergroups WHERE person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        if (empty($data['groups'])) {
            return;
        }

        foreach ($data['groups'] as $group) {
            $this->tableInsert('person2usergroups', [
                'person_id'    => $person->getId(),
                'usergroup_id' => (int) $group,
            ]);
        }
    }

    /**
     * @param Person $person
     * @param array  $data
     */
    protected function restoreBrands(Person $person, $data)
    {
        if (!array_key_exists('brands', $data)) {
            return;
        }

        $this->em->getConnection()->executeUpdate(
            'DELETE FROM person_to_brand WHERE person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        if (empty($data['brands'])) {
            return;
        }

        foreach ($data['brands'] as $brand) {
            $this->tableInsert('person_to_brand', [
                'person_id' => $person->getId(),
                'brand_id'  => (int) $brand,
            ]);
        }
    }

    /**
     * @param Person $person
     * @param array  $data
     */
    protected function restoreAgentTeams(Person $person, $data)
    {
        if (!array_key_exists('agent_teams', $data)) {
            return;
        }

        $this->em->getConnection()->executeUpdate(
            'DELETE FROM agent_team_members WHERE person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        if (empty($data['agent_teams'])) {
            return;
        }

        foreach ($data['agent_teams'] as $team) {
            $this->tableInsert('agent_team_members', [
                'person_id' => $person->getId(),
                'team_id'   => (int) $team,
            ]);
        }
    }

    /**
     * @param Person $person
     * @param array  $data
     */
    protected function restoreRelatedTables(Person $person, $data)
    {
        if (!isset($data['related_tables_ids']) || empty($data['related_tables_ids'])) {
            return;
        }

        foreach ($data['related_tables_ids'] as $tableName => $fkNames) {
            foreach ($fkNames as $fkName => $ids) {
                if (!$ids) {
                    continue;
                }

                $this->em->getConnection()->executeUpdate("
                    UPDATE IGNORE $tableName
                    SET $fkName = :personId
                    WHERE id IN (:ids)
                ",
                    [
                        'personId' => $person->getId(),
                        'ids'      => $ids,
                    ],
                    [
                        'personId' => \PDO::PARAM_INT,
                        'ids'      => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
                    ]
                );
            }
        }
    }

    /**
     * Overwrite Doctrine\DBAL\Connection::insert to support `insert ignore`.
     * Need this in case if some dump data not exists anymore.
     * For example when insert teams id's which are not exist.
     *
     * @param string $tableExpression
     * @param array  $data
     *
     * @return int
     */
    protected function tableInsert($tableExpression, array $data)
    {
        $columnList        = [];
        $paramPlaceholders = [];
        $paramValues       = [];

        foreach ($data as $columnName => $value) {
            $columnList[]        = $columnName;
            $paramPlaceholders[] = '?';
            $paramValues[]       = $value;
        }

        return $this->em->getConnection()->executeUpdate(
            'INSERT IGNORE INTO '.$tableExpression.' ('.implode(', ', $columnList).')'.
            ' VALUES ('.implode(', ', $paramPlaceholders).')',
            $paramValues
        );
    }
}
