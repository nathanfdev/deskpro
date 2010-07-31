<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category HttpFoundation
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\HttpFoundation\SessionStorage;

use Orb\Util\Strings;
use Orb\Util\Util;


/**
 * This storage uses the Session entity for storing session info.
 */
class SessionEntityStorage extends \Symfony\Components\HttpFoundation\SessionStorage\NativeSessionStorage
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em, $options = null)
    {
        $this->em = $em;
        parent::__construct($options);
    }



    /**
     * Starts the session.
     */
    public function start()
    {
        if (self::$sessionStarted) {
            return;
        }

        // use this object as the session handler
        session_set_save_handler(
            array($this, 'sessionOpen'),
            array($this, 'sessionClose'),
            array($this, 'sessionRead'),
            array($this, 'sessionWrite'),
            array($this, 'sessionDestroy'),
            array($this, 'sessionGC')
        );

        parent::start();
    }



    /**
     * Opens a session.
     *
     * @param  string $path  (ignored)
     * @param  string $name  (ignored)
     *
     * @return boolean true, if the session was opened, otherwise an exception is thrown
     */
    public function sessionOpen($path = null, $name = null)
    {
        return true;
    }



    /**
     * Closes a session.
     *
     * @return boolean true, if the session was closed, otherwise false
     */
    public function sessionClose()
    {
        // do nothing
        return true;
    }



    /**
     * Destroys a session.
     *
     * @param  string $id  A session ID
     *
     * @return bool   true, if the session was destroyed, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be destroyed
     */
    public function sessionDestroy($id)
    {
        // get table/column
        $db_table  = $this->options['db_table'];
        $db_id_col = $this->options['db_id_col'];

        // delete the record associated with this id
        $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_id_col.'= ?';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $id, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }

        return true;
    }



    /**
     * Cleans up old sessions.
     *
     * @param  int $lifetime  The lifetime of a session in seconds
     * @return bool true
     * @throws \RuntimeException If any old sessions cannot be cleaned
     */
    public function sessionGC($lifetime)
    {
		$conn = $this->em->getConnection();

		$datetime = date('Y-m-d H:i:s', time() - $lifetime);

        try {
			$conn->executeQuery('DELETE FROM sessions WHERE updated_at < ?', array($datetime));
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }

        return true;
    }



    /**
     * Reads a session.
     *
     * @param  string $id  A session ID
     *
     * @return string      The session data if the session was read or created, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be read
     */
    public function sessionRead($id)
    {
        // get table/columns
        $db_table    = $this->options['db_table'];
        $db_data_col = $this->options['db_data_col'];
        $db_id_col   = $this->options['db_id_col'];
        $db_time_col = $this->options['db_time_col'];

        try {
            $sql = 'SELECT '.$db_data_col.' FROM '.$db_table.' WHERE '.$db_id_col.'=?';

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $id, \PDO::PARAM_STR, 255);

            $stmt->execute();
            // it is recommended to use fetchAll so that PDO can close the DB cursor
            // we anyway expect either no rows, or one row with one column. fetchColumn, seems to be buggy #4777
            $sessionRows = $stmt->fetchAll(\PDO::FETCH_NUM);

            if (count($sessionRows) == 1) {
                return $sessionRows[0][0];
            } else {
                // session does not exist, create it
                $sql = 'INSERT INTO '.$db_table.'('.$db_id_col.', '.$db_data_col.', '.$db_time_col.') VALUES (?, ?, ?)';

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(1, $id, \PDO::PARAM_STR);
                $stmt->bindValue(2, '', \PDO::PARAM_STR);
                $stmt->bindValue(3, time(), \PDO::PARAM_INT);
                $stmt->execute();

                return '';
            }
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }
    }



    /**
     * Writes session data.
     *
     * @param  string $id    A session ID
     * @param  string $data  A serialized chunk of session data
     *
     * @return bool true, if the session was written, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session data cannot be written
     */
    public function sessionWrite($id, $data)
    {
        // get table/column
        $db_table    = $this->options['db_table'];
        $db_data_col = $this->options['db_data_col'];
        $db_id_col   = $this->options['db_id_col'];
        $db_time_col = $this->options['db_time_col'];

        $sql = 'UPDATE '.$db_table.' SET '.$db_data_col.' = ?, '.$db_time_col.' = '.time().' WHERE '.$db_id_col.'= ?';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $data, \PDO::PARAM_STR);
            $stmt->bindParam(2, $id, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }

        return true;
    }
}
