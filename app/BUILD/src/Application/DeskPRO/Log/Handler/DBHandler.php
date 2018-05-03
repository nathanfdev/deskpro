<?php

namespace Application\DeskPRO\Log\Handler;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

abstract class DBHandler extends AbstractProcessingHandler
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;
    /** @var array */
    protected $statements = [];
    /** @var array */
    protected $meta = [];

    public function __construct(EntityManager $em, $level = Logger::DEBUG, $bubble = true)
    {
        $this->em = $em;
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return isset($record['context']['_entity']);
    }

    /**
     * create/return prepared insert statement.
     *
     * @param DomainObject $entity
     *
     * @return \Doctrine\DBAL\Driver\Statement|\Doctrine\DBAL\Statement
     */
    protected function getStatement(DomainObject $entity)
    {
        $class = get_class($entity);
        if (isset($this->statements[$class])) {
            return $this->statements[$class];
        }

        $meta = $this->getMeta($entity);

        return $this->statements[$class] = $this->em->getConnection()->prepare(sprintf('
                INSERT INTO %s (%s) VALUES (%s)
            ',
            $meta['table'],
            implode(', ', array_keys($meta['fields'])),
            ':'.implode(', :', array_values($meta['fields']))
        ));
    }

    /**
     * table/fields metadata.
     *
     * @param DomainObject $entity
     *
     * @return array
     */
    protected function getMeta(DomainObject $entity)
    {
        $class = get_class($entity);
        if (isset($this->meta[$class])) {
            return $this->meta[$class];
        }

        $data               = $this->em->getUnitOfWork()->getEntityPersister($class)->getClassMetadata();
        $this->meta[$class] = [
            'table'  => $data->table['name'],
            'fields' => $data->fieldNames,
        ];

        // todo use BasicEntityPersister methods to create and bind statement
        foreach ($data->associationMappings as $property => $mapping) {
            if ($mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE) {
                foreach ($mapping['joinColumns'] as $joinColumn) {
                    $this->meta[$class]['fields'][$joinColumn['name']] = $property;
                }
            }
        }

        return $this->meta[$class];
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $entity = $record['context']['_entity'];
        $meta   = $this->getMeta($entity);
        $stmt   = $this->getStatement($entity);
        $data   = [];
        foreach ($meta['fields'] as $fieldName) {
            $data[$fieldName] = $entity[$fieldName] instanceof DomainObject
                ? $entity[$fieldName]['id'] // todo
                : $entity[$fieldName];

            // todo
            if (is_array($data[$fieldName])) {
                $data[$fieldName] = serialize($data[$fieldName]);
            }
        }

        // todo try/catch block?

        $stmt->execute($data);
        if ($id = $this->em->getConnection()->lastInsertId()) {
            $entity['id'] = $id;
        }
        $stmt->closeCursor();
    }
}
