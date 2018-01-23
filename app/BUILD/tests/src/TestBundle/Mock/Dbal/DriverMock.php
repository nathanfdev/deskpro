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

class DriverMock implements \Doctrine\DBAL\Driver
{
    private $_platformMock;
    private $_schemaManagerMock;
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new DriverConnectionMock();
    }
    /**
     * Constructs the Sqlite PDO DSN.
     *
     * @return string The DSN
     * @override
     */
    protected function _constructPdoDsn(array $params)
    {
        return '';
    }
    /**
     * @override
     */
    public function getDatabasePlatform()
    {
        if (!$this->_platformMock) {
            $this->_platformMock = new DatabasePlatformMock();
        }

        return $this->_platformMock;
    }
    /**
     * @override
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        if ($this->_schemaManagerMock == null) {
            return new SchemaManagerMock($conn);
        } else {
            return $this->_schemaManagerMock;
        }
    }
    /* MOCK API */
    public function setDatabasePlatform(\Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        $this->_platformMock = $platform;
    }
    public function setSchemaManager(\Doctrine\DBAL\Schema\AbstractSchemaManager $sm)
    {
        $this->_schemaManagerMock = $sm;
    }
    public function getName()
    {
        return 'mock';
    }
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        return;
    }
    public function convertExceptionCode(\Exception $exception)
    {
        return 0;
    }
}
