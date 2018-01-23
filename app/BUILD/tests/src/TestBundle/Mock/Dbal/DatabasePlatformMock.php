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

namespace DpTestSrc\TestBundle\Mock\Dbal;

use Doctrine\DBAL\DBALException;

class DatabasePlatformMock extends \Doctrine\DBAL\Platforms\AbstractPlatform
{
    private $_sequenceNextValSql     = '';
    private $_prefersIdentityColumns = true;
    private $_prefersSequences       = false;
    /**
     * @override
     */
    public function prefersIdentityColumns()
    {
        return $this->_prefersIdentityColumns;
    }
    /**
     * @override
     */
    public function prefersSequences()
    {
        return $this->_prefersSequences;
    }
    /** @override */
    public function getSequenceNextValSQL($sequenceName)
    {
        return $this->_sequenceNextValSql;
    }
    /** @override */
    public function getBooleanTypeDeclarationSQL(array $field)
    {
    }
    /** @override */
    public function getIntegerTypeDeclarationSQL(array $field)
    {
    }
    /** @override */
    public function getBigIntTypeDeclarationSQL(array $field)
    {
    }
    /** @override */
    public function getSmallIntTypeDeclarationSQL(array $field)
    {
    }
    /** @override */
    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef)
    {
    }
    /** @override */
    public function getVarcharTypeDeclarationSQL(array $field)
    {
    }
    /** @override */
    public function getClobTypeDeclarationSQL(array $field)
    {
    }
    /* MOCK API */
    public function setPrefersIdentityColumns($bool)
    {
        $this->_prefersIdentityColumns = $bool;
    }
    public function setPrefersSequences($bool)
    {
        $this->_prefersSequences = $bool;
    }
    public function setSequenceNextValSql($sql)
    {
        $this->_sequenceNextValSql = $sql;
    }
    public function getName()
    {
        return 'mock';
    }
    protected function initializeDoctrineTypeMappings()
    {
    }
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed)
    {
    }
    /**
     * Gets the SQL Snippet used to declare a BLOB column type.
     */
    public function getBlobTypeDeclarationSQL(array $field)
    {
        throw DBALException::notSupported(__METHOD__);
    }
}
