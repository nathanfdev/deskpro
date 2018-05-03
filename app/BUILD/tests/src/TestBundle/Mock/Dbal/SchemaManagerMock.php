<?php

namespace DpTestSrc\TestBundle\Mock\Dbal;

class SchemaManagerMock extends \Doctrine\DBAL\Schema\AbstractSchemaManager
{
    public function __construct(\Doctrine\DBAL\Connection $conn)
    {
        parent::__construct($conn);
    }

    protected function _getPortableTableColumnDefinition($tableColumn)
    {
    }
}
