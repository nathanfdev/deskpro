<?php

namespace DeskPRO\Bundle\InstallBundle\Schema;

use DeskPRO\Bundle\InstallBundle\Schema\Exception\SchemaInstallException;

class SchemaInstaller
{
    /**
     * @var SchemaInterface
     */
    private $schema;

    /**
     * SchemaInstaller constructor.
     *
     * @param SchemaInterface $schema
     */
    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param \PDO     $db
     * @param callable $stepper A custom callable to run after each query
     *
     * @throws SchemaInstallException
     *
     * @return bool
     */
    public function installSchema(\PDO $db, $stepper = null)
    {
        foreach ($this->schema->getCreates() as $idx => $query) {
            try {
                $db->exec($query);
            } catch (\Exception $e) {
                throw new SchemaInstallException($query, $e->getMessage(), $e->getCode(), $e);
            }

            if ($stepper) {
                call_user_func($stepper, [
                    'type'  => 'create',
                    'idx'   => $idx,
                    'query' => $query,
                ]);
            }
        }

        foreach ($this->schema->getAlters() as $idx => $query) {
            try {
                $db->exec($query);
            } catch (\Exception $e) {
                throw new SchemaInstallException($query, $e->getMessage(), $e->getCode(), $e);
            }

            if ($stepper) {
                call_user_func($stepper, [
                    'type'  => 'alter',
                    'idx'   => $idx,
                    'query' => $query,
                ]);
            }
        }

        return true;
    }
}
