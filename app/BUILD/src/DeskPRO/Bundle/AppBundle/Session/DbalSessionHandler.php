<?php

namespace DeskPRO\Bundle\AppBundle\Session;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\DriverException;
use Doctrine\DBAL\Platforms\SQLServer2008Platform;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * DeskPRO.
 */
class DbalSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var Connection
     */
    protected $con;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string Column for session id
     */
    protected $idCol;

    /**
     * @var string Column for session data
     */
    protected $dataCol;

    /**
     * @var string Column for timestamp
     */
    protected $timeCol;

    /**
     * @var VisitorIdentificationProvider
     */
    private $visitor_id_provider;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    /**
     * Constructor.
     *
     * @param Connection                    $con                 A connection
     * @param string                        $tableName           Table name
     * @param VisitorIdentificationProvider $visitor_id_provider
     * @param TokenStorage                  $token_storage
     */
    public function __construct(Connection $con, $tableName, VisitorIdentificationProvider $visitor_id_provider, TokenStorage $token_storage)
    {
        $this->con                 = $con;
        $this->table               = $tableName;
        $this->idCol               = 'sess_id';
        $this->dataCol             = 'sess_data';
        $this->timeCol             = 'sess_time';
        $this->visitor_id_provider = $visitor_id_provider;
        $this->token_storage       = $token_storage;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        // delete the record associated with this id
        $sql = "DELETE FROM $this->table WHERE $this->idCol = :id";

        try {
            $stmt = $this->con->prepare($sql);
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf('Exception was thrown when trying to delete a session: %s', $e->getMessage()), 0, $e
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        // delete the session records that have expired
        $sql = "DELETE FROM $this->table WHERE $this->timeCol < :time";

        try {
            $stmt = $this->con->prepare($sql);
            $stmt->bindValue(':time', time() - $maxlifetime, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf('Exception was thrown when trying to delete expired sessions: %s', $e->getMessage()), 0, $e
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $sql = "SELECT $this->dataCol FROM $this->table WHERE $this->idCol = :id";

        try {
            $stmt = $this->con->prepare($sql);
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();

            // We use fetchAll instead of fetchColumn to make sure the DB cursor gets closed
            $sessionRows = $stmt->fetchAll(\PDO::FETCH_NUM);

            if ($sessionRows) {
                return base64_decode($sessionRows[0][0]);
            }

            return '';
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf('Exception was thrown when trying to read the session data: %s', $e->getMessage()), 0, $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $encoded = base64_encode($data);

        try {
            $person_id = null;
            if ($token = $this->token_storage->getToken()) {
                if ($person = $token->getUser()) {
                    if ($person instanceof Person) {
                        $person_id = $person->getId();
                    }
                }
            }
            $visitor_id = $this->visitor_id_provider->getVisitorIdentifier();
            // We use a single MERGE SQL query when supported by the database.
            $mergeSql = $this->getMergeSql();

            if (null !== $mergeSql) {
                $mergeStmt = $this->con->prepare($mergeSql);
                $mergeStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                $mergeStmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
                $mergeStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                $mergeStmt->bindValue(':visitor_id', $visitor_id, \PDO::PARAM_STR);
                $mergeStmt->bindValue(':person_id', $person_id, \PDO::PARAM_INT);
                $mergeStmt->execute();

                return true;
            }

            $updateStmt = $this->con->prepare(
                "UPDATE $this->table SET $this->dataCol = :data, $this->timeCol = :time WHERE $this->idCol = :id, visitor_id = :visitor_id, person_id = :person_id"
            );
            $updateStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $updateStmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
            $updateStmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $updateStmt->bindValue(':visitor_id', $visitor_id, \PDO::PARAM_STR);
            $updateStmt->bindValue(':person_id', $person_id, \PDO::PARAM_INT);
            $updateStmt->execute();

            // When MERGE is not supported, like in Postgres, we have to use this approach that can result in
            // duplicate key errors when the same session is written simultaneously. We can just catch such an
            // error and re-execute the update. This is similar to a serializable transaction with retry logic
            // on serialization failures but without the overhead and without possible false positives due to
            // longer gap locking.
            if (!$updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->con->prepare(
                        "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->timeCol, visitor_id, person_id) VALUES (:id, :data, :time, :visitor_id, :person_id)"
                    );
                    $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                    $insertStmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
                    $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                    $updateStmt->bindValue(':visitor_id', $visitor_id, \PDO::PARAM_STR);
                    $updateStmt->bindValue(':person_id', $person_id, \PDO::PARAM_INT);
                    $insertStmt->execute();
                } catch (\Exception $e) {
                    $driverException = $e->getPrevious();
                    // Handle integrity violation SQLSTATE 23000 (or a subclass like 23505 in Postgres) for duplicate keys
                    // DriverException only available since DBAL 2.5
                    if (
                        ($driverException instanceof DriverException && 0 === strpos(
                                $driverException->getSQLState(), '23'
                            )) ||
                        ($driverException instanceof \PDOException && 0 === strpos($driverException->getCode(), '23'))
                    ) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf('Exception was thrown when trying to write the session data: %s', $e->getMessage()), 0, $e
            );
        }

        return true;
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) SQL query when supported by the database.
     *
     * @return string|null The SQL string or null when not supported
     */
    protected function getMergeSql()
    {
        $platform = $this->con->getDatabasePlatform()->getName();

        switch ($platform) {
            case 'mysql':
                return "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->timeCol, visitor_id, person_id) VALUES (:id, :data, :time, :visitor_id, :person_id) ".
                "ON DUPLICATE KEY UPDATE $this->dataCol = VALUES($this->dataCol), $this->timeCol = VALUES($this->timeCol)";
            case 'oracle':
                // DUAL is Oracle specific dummy table
                return "MERGE INTO $this->table USING DUAL ON ($this->idCol = :id) ".
                "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->timeCol, visitor_id, person_id) VALUES (:id, :data, :time, :visitor_id, :person_id) ".
                "WHEN MATCHED THEN UPDATE SET $this->dataCol = :data, $this->timeCol = :time";
            case $this->con->getDatabasePlatform() instanceof SQLServer2008Platform:
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return "MERGE INTO $this->table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ($this->idCol = :id) ".
                "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->timeCol, visitor_id, person_id) VALUES (:id, :data, :time, :visitor_id, :person_id) ".
                "WHEN MATCHED THEN UPDATE SET $this->dataCol = :data, $this->timeCol = :time;";
            case 'sqlite':
                return "INSERT OR REPLACE INTO $this->table ($this->idCol, $this->dataCol, $this->timeCol, visitor_id, person_id) VALUES (:id, :data, :time, :visitor_id, :person_id)";
        }
    }
}
