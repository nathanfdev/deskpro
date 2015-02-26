<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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


namespace Application\DeskPRO\Log\Handler;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

abstract class DBHandler extends AbstractProcessingHandler
{
    /** @var \Doctrine\ORM\EntityManager  */
    protected $em;
    /** @var array */
    protected $statements = array();
    /** @var array */
    protected $meta = array();

    public function __construct(EntityManager $em, $level = Logger::DEBUG, $bubble = true)
    {
        $this->em = $em;
        parent::__construct($level, $bubble);
    }

    /**
     * @inheritdoc
     */
    public function isHandling(array $record)
    {
        return isset($record['context']['_entity']);
    }

    /**
     * create/return prepared insert statement
     * @param  DomainObject                                             $entity
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
            ':' . implode(', :', array_values($meta['fields']))
        ));
    }

    /**
     * table/fields metadata
     * @param  DomainObject $entity
     * @return array
     */
    protected function getMeta(DomainObject $entity)
    {
        $class = get_class($entity);
        if (isset($this->meta[$class])) {
            return $this->meta[$class];
        }

        $data = $this->em->getUnitOfWork()->getEntityPersister($class)->getClassMetadata();
        $this->meta[$class] = array(
            'table' => $data->table['name'],
            'fields' => $data->fieldNames,
        );

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
        $meta = $this->getMeta($entity);
        $stmt = $this->getStatement($entity);
        $data = array();
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
