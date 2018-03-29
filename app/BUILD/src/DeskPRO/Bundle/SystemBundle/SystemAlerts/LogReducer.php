<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts;

use Application\DeskPRO\DBAL\Connection;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;

/**
 * Class LogReducer.
 *
 * Provides events log pre- and post-processing reducers. Need to split reduction into two separate processes because
 * reducing only after processing we can run into performance issues when some event occurs a lot within small
 * periods of time. And we can't reduce only before processing as need to preserve existing events for processing
 * (they are needed to determine incidental states) and also need to clean up some of them after processing is done.
 */
class LogReducer
{
    /**
     * @var int Max number of events of the same type, applied to events with quantity expiration strategy. This number
     *          must be sufficient to raise any incident
     */
    private $quantityLimit = 150;

    /**
     * @var int Event max alive time in minutes, applied to events with time expiration strategy. This period must be
     *          sufficient to raise any incident
     */
    private $timeLimit = 4320; // "60 * 24 * 3" PhpLint doesn't allow expression here

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var array Used by the getEventSubjectIds() to cache its' result
     */
    private $eventSubjectIdsCache = [];

    /**
     * @var array Used by the getPreservedEventIds() to cache its' result
     */
    private $preservedEventIdsCache = [];

    /**
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param int $quantityLimit
     */
    public function setQuantityLimit($quantityLimit)
    {
        $this->quantityLimit = $quantityLimit;
    }

    /**
     * @param int $timeLimit
     */
    public function setTimeLimit($timeLimit)
    {
        $this->timeLimit = $timeLimit;
    }

    /**
     * Pre-process log reducer.
     *
     * - Reduces new events by quantity
     * - Reduces new events by time periods
     * - Reduces new events time periods to quantity
     */
    public function preProcessingReducer()
    {
        $this->flushCache();
        $this->reduceToQuantity(false);
        $this->reduceToTimePeriods(false);
        $this->reduceTimePeriodsToQuantity(false);
    }

    /**
     * Post-process log reducer.
     *
     * - Reduces processed events by quantity
     * - Reduces processed events time periods to quantity
     *
     * Unlike pre-process in doesn't reduce by time periods to not make irrelevant the existing incidents
     */
    public function postProcessingReducer()
    {
        $this->flushCache();
        $this->reduceToQuantity(true);
        $this->reduceTimePeriodsToQuantity(true);
    }

    /**
     * Reduces events number with the same event subject.
     *
     * This prevents from having extra events with quantity based expiration strategy
     *
     * @param bool $processed
     */
    private function reduceToQuantity($processed)
    {
        $processed       = $processed ? 1 : 0;
        $eventSubjectIds = $this->getEventSubjectIds(AbstractEvent::EXPIRES_WITH_QUANTITY);
        foreach ($eventSubjectIds as $eventSubjectId) {
            $preserveIds = $this->getPreservedEventIds($eventSubjectId);

            $limit = $this->quantityLimit - count($preserveIds);
            if ($limit > 0) {
                $preserveIds = array_merge($preserveIds, $this->queryTopIds($eventSubjectId, $limit, $processed));
            }
            $this->conn->executeUpdate(
                'DELETE FROM `system_alerts_events` WHERE subject_unique_id = ? AND processed = ? AND id NOT IN (?)',
                [$eventSubjectId, $processed, $preserveIds],
                [\PDO::PARAM_STR, \PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]
            );
        }
    }

    /**
     * Reduces events number with the same event subject (applied to events with time based expiration strategy).
     *
     * This prevents from having extra events with time based expiration strategy.
     *
     * Selects the last event date and preserves only those events, which are not older than the last event date minus
     * the configured time limit.
     *
     * @param bool $processed
     */
    private function reduceToTimePeriods($processed)
    {
        $processed        = $processed ? 1 : 0;
        $eventSubjectIds  = $this->getEventSubjectIds(AbstractEvent::EXPIRES_WITH_TIME);
        $lastEventDateSql = 'SELECT MAX(date_created) FROM `system_alerts_events` WHERE subject_unique_id = ?';
        foreach ($eventSubjectIds as $eventSubjectId) {
            $lastEventDate = $this->query($lastEventDateSql, $eventSubjectId);
            $reduceSql     = '
                DELETE FROM `system_alerts_events`
                WHERE subject_unique_id = ? AND processed = ? AND date_created < ? - INTERVAL ? MINUTE
            ';
            $this->query($reduceSql, [$eventSubjectId, $processed, $lastEventDate, $this->timeLimit], false);
        }
    }

    /**
     * Reduces events within the time limit periods to quantity limit.
     *
     * This prevents from having extra events within allowed periods for events with time based expiration strategy.
     *
     * Removes all events except the first one and the last (quantity limit - 1) so that each period has no more than
     * the allowed quantity limit.
     *
     * @param bool $processed
     */
    private function reduceTimePeriodsToQuantity($processed)
    {
        $processed       = $processed ? 1 : 0;
        $eventSubjectIds = $this->getEventSubjectIds(AbstractEvent::EXPIRES_WITH_TIME);
        foreach ($eventSubjectIds as $eventSubjectId) {
            $preserveIds   = $this->getPreservedEventIds($eventSubjectId);
            $preserveIds[] = $this->query(
                'SELECT MIN(id) FROM `system_alerts_events` WHERE subject_unique_id = ?', $eventSubjectId);
            $preserveIds = array_unique($preserveIds);

            $limit = $this->quantityLimit - count($preserveIds);
            if ($limit > 0) {
                $topIds      = $this->queryTopIds($eventSubjectId, $limit, $processed);
                $preserveIds = array_merge($preserveIds, $topIds);
            }

            if (count($preserveIds) === $this->quantityLimit) {
                $this->conn->executeUpdate(
                    'DELETE FROM system_alerts_events WHERE subject_unique_id = ? AND processed = ? AND id NOT IN (?)',
                    [$eventSubjectId, $processed, $preserveIds],
                    [\PDO::PARAM_STR, \PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]
                );
            }
        }
    }

    /**
     * Get set of unique event subject IDs.
     *
     * @param string $expirationStrategy
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     *
     * @return array
     */
    private function getEventSubjectIds($expirationStrategy)
    {
        if (!in_array($expirationStrategy, [AbstractEvent::EXPIRES_WITH_QUANTITY, AbstractEvent::EXPIRES_WITH_TIME])) {
            throw new \Exception('Invalid Event expiration strategy '.$expirationStrategy);
        }

        if (!array_key_exists($expirationStrategy, $this->eventSubjectIdsCache)) {
            $sql  = 'SELECT DISTINCT subject_unique_id FROM `system_alerts_events` WHERE expiration_strategy = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$expirationStrategy]);
            $this->eventSubjectIdsCache[$expirationStrategy] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }

        return $this->eventSubjectIdsCache[$expirationStrategy];
    }

    /**
     * @param string $eventSubjectId Optional subject_unique_id
     *
     * @return array
     */
    private function getPreservedEventIds($eventSubjectId)
    {
        if (!array_key_exists($eventSubjectId, $this->preservedEventIdsCache)) {
            $ids = $this->queryAll('
                SELECT DISTINCT first_failure_event_id FROM system_alerts_incidents WHERE subject_unique_id=?
                UNION SELECT DISTINCT last_failure_event_id FROM system_alerts_incidents WHERE subject_unique_id=?
            ', [$eventSubjectId, $eventSubjectId]);
            $this->preservedEventIdsCache[$eventSubjectId] = array_filter($ids, function ($id) {
                return !is_null($id);
            });
        }

        return $this->preservedEventIdsCache[$eventSubjectId];
    }

    /**
     * @param string   $eventSubjectId
     * @param int      $limit
     * @param int|bool $processed
     *
     * @return array
     */
    private function queryTopIds($eventSubjectId, $limit, $processed)
    {
        $processed = $processed ? 1 : 0;
        $limit     = intval($limit);
        $sql       = "
            SELECT id
            FROM `system_alerts_events`
            WHERE subject_unique_id = ? AND processed = ?
            ORDER BY id DESC
            LIMIT $limit
        ";
        $ids = $this->queryAll($sql, [$eventSubjectId, $processed]);
        $ids = array_map(function ($id) {
            return intval($id);
        }, $ids);

        return $ids;
    }

    /**
     * Flush cache variables.
     */
    private function flushCache()
    {
        $this->eventSubjectIdsCache   = [];
        $this->preservedEventIdsCache = [];
    }

    /**
     * @param string       $sql
     * @param array|string $params
     * @param int          $mode
     *
     * @return mixed
     */
    private function query($sql, $params = [], $mode = \PDO::FETCH_COLUMN)
    {
        is_array($params) or $params = [$params];

        $prepared = $this->conn->prepare($sql);
        $prepared->execute($params);

        if ($mode) {
            return $prepared->fetch($mode);
        }
    }

    /**
     * @param string       $sql
     * @param array|string $params
     * @param int          $mode
     *
     * @return mixed
     */
    private function queryAll($sql, $params = [], $mode = \PDO::FETCH_COLUMN)
    {
        is_array($params) or $params = [$params];

        $prepared = $this->conn->prepare($sql);
        $prepared->execute($params);

        return $prepared->fetchAll($mode);
    }
}
