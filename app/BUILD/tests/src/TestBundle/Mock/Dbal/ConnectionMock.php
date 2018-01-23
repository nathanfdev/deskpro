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

class ConnectionMock extends \Doctrine\DBAL\Connection
{
    private $_fetchOneResult;
    private $_platformMock;
    private $_lastInsertId = 0;
    private $_inserts      = [];

    /**
     * @return ConnectionMock
     */
    public static function create()
    {
        return new self([], new DriverMock(), null, null);
    }

    public function __construct(array $params, $driver, $config = null, $eventManager = null)
    {
        $this->_platformMock = new DatabasePlatformMock();
        $params['platform']  = $this->_platformMock;
        parent::__construct($params, $driver, $config, $eventManager);
    }
    /**
     * @override
     */
    public function getDatabasePlatform()
    {
        return $this->_platformMock;
    }
    /**
     * @override
     */
    public function insert($tableName, array $data, array $types = [])
    {
        $this->_inserts[$tableName][] = $data;
    }
    /**
     * @override
     */
    public function lastInsertId($seqName = null)
    {
        return $this->_lastInsertId;
    }
    /**
     * @override
     */
    public function fetchColumn($statement, array $params = [], $colnum = 0, array $types = [])
    {
        return $this->_fetchOneResult;
    }
    /**
     * @override
     */
    public function quote($input, $type = null)
    {
        if (is_string($input)) {
            return "'".$input."'";
        }

        return $input;
    }
    /* Mock API */
    public function setFetchOneResult($fetchOneResult)
    {
        $this->_fetchOneResult = $fetchOneResult;
    }
    public function setLastInsertId($id)
    {
        $this->_lastInsertId = $id;
    }
    public function getInserts()
    {
        return $this->_inserts;
    }
    public function reset()
    {
        $this->_inserts      = [];
        $this->_lastInsertId = 0;
    }
}
