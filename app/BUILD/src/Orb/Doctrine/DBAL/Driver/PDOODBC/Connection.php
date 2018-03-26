<?php

/**
 * Orb.
 */

namespace Orb\Doctrine\DBAL\Driver\PDOODBC;

class Connection extends \Doctrine\DBAL\Driver\PDOConnection implements \Doctrine\DBAL\Driver\Connection
{
    /** @var bool|null */
    protected $_pdoTransactionsSupport = null;
    /** @var bool|null */
    protected $_pdoLastInsertIdSupport = null;

    /**
     * @override
     */
    public function quote($value, $type = \PDO::PARAM_STR)
    {
        $val = parent::quote($value, $type);

        // Some PDO drivers dont implement quote(), so we need to do ourselves
        // This is a rther dumb 'escape' where we just remove bad chars and then quote
        if (!$val && $value) {
            if (is_numeric($value)) {
                $val = $value;
            } else {
                if ($value === null) {
                    return 'NULL';
                }
                if ($value === '') {
                    return '';
                }
                if ($value === true) {
                    return 1;
                }
                if ($value === false) {
                    return 0;
                }
                if (is_numeric($value)) {
                    return $value;
                }

                $non_displayables = [
                    '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
                    '/%1[0-9a-f]/',             // url encoded 16-31
                    '/[\x00-\x08]/',            // 00-08
                    '/\x0b/',                   // 11
                    '/\x0c/',                   // 12
                    '/[\x0e-\x1f]/',             // 14-31
                ];
                foreach ($non_displayables as $regex) {
                    $value = preg_replace($regex, '', $value);
                }

                $value = str_replace("'", "''", $value);

                return "'".$value."'";
            }
        }

        return $val;
    }

    /**
     * @return bool transaction support
     */
    private function _pdoTransactionsSupported()
    {
        if (!is_null($this->_pdoTransactionsSupport)) {
            return $this->_pdoTransactionsSupport;
        }

        try {
            $supported = true;
            parent::beginTransaction();
        } catch (\PDOException $e) {
            $supported = false;
        }
        if ($supported) {
            parent::commit();
        }

        return $this->_pdoTransactionsSupport = $supported;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        if ($this->_pdoTransactionsSupported() === true) {
            parent::rollback();
        } else {
            $this->exec('ROLLBACK TRANSACTION');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if ($this->_pdoTransactionsSupported() === true) {
            parent::commit();
        } else {
            $this->exec('COMMIT TRANSACTION');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        if ($this->_pdoTransactionsSupported() === true) {
            parent::beginTransaction();
        } else {
            $this->exec('BEGIN TRANSACTION');
        }
    }

    /**
     * @return bool lastInsertId support
     */
    private function _pdoLastInsertId()
    {
        if (!is_null($this->_pdoLastInsertIdSupport)) {
            return $this->_pdoLastInsertIdSupport;
        }

        try {
            $supported = true;
            parent::lastInsertId();
        } catch (\PDOException $e) {
            $supported = false;
        }

        return $this->_pdoLastInsertIdSupport = $supported;
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null)
    {
        $id = null;
        if ($this->_pdoLastInsertId() === true) {
            $id = parent::lastInsertId();
        } else {
            $stmt = $this->query('SELECT SCOPE_IDENTITY()');
            $id   = $stmt->fetchColumn();
            $stmt->closeCursor();
        }

        return $id;
    }
}
