<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DBAL\Platforms;

use Doctrine\DBAL\Schema\Index;

class MySqlPlatform extends \Doctrine\DBAL\Platforms\MySqlPlatform
{
    protected function initializeDoctrineTypeMappings()
    {
        parent::initializeDoctrineTypeMappings();

        $this->doctrineTypeMapping['longblob']   = 'dpblob_file';
        $this->doctrineTypeMapping['blob']       = 'dpblob_file';
        $this->doctrineTypeMapping['mediumblob'] = 'dpblob_file';
        $this->doctrineTypeMapping['tinyblob']   = 'dpblob_file';
        $this->doctrineTypeMapping['varbinary']  = 'dpblob';
    }

    public function getIndexDeclarationSQL($name, Index $index)
    {
        $sql = parent::getIndexDeclarationSQL($name, $index);

        if (strpos($sql, 'searchlog_query_idx') !== null) {
            $sql = str_replace('ON searchlog (query)', 'ON searchlog (query (15))', $sql);
            $sql = str_replace('searchlog_query_idx (query)', 'searchlog_query_idx (query (15))', $sql);
        }

        return $sql;
    }

    public function getCreateIndexSQL(Index $index, $table)
    {
        $sql = parent::getCreateIndexSQL($index, $table);

        if (strpos($sql, 'searchlog_query_idx') !== null) {
            $sql = str_replace('ON searchlog (query)', 'ON searchlog (query (15))', $sql);
            $sql = str_replace('searchlog_query_idx (query)', 'searchlog_query_idx (query (15))', $sql);
        }

        return $sql;
    }
}
