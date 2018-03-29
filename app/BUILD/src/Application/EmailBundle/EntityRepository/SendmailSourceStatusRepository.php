<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\EmailBundle\EntityRepository;

use Application\DeskPRO\EntityRepository\AbstractEntityRepository;

class SendmailSourceStatusRepository extends AbstractEntityRepository
{
    const LIMIT = 100;

    protected $queries = [];

    protected $params = [];

    protected $refs = [];

    /**
     * preparing queries.
     *
     * @param $ref
     * @param $email
     * @param $event
     * @param $info
     * @param $details
     */
    public function enqueueStatus($ref, $email, $event, $info, $details)
    {
        $k = count($this->queries);

        $this->queries[] = "insert into {$this->getTableName()} (sendmail_source_id, user_email, event_type, event_info, details, date_created)
				values (:ssid$k, :email$k, :event$k, :info$k, :details$k, now());";

        $this->params[':ssid'.$k]    = null;
        $this->params[':email'.$k]   = $email;
        $this->params[':event'.$k]   = $event;
        $this->params[':info'.$k]    = $info;
        $this->params[':details'.$k] = $details;

        if ('ok' !== $info) {
            @$this->refs[$ref]['errors']++;
        } elseif ('delivered' === $event) {
            // count only delivered events
            @$this->refs[$ref]['success']++;
        }
        $this->refs[$ref]['keys'][] = ':ssid'.$k;
        $this->refs[$ref]['id']     = null;

        if ($k >= self::LIMIT) {
            $this->flush();
        }
    }

    /**
     * preparing update queries to update sendmail sources.
     */
    protected function queryCounts()
    {
        $ss = $this->getEntityManager()->getRepository('EmailBundle:SendmailSource');

        foreach ($this->refs as $ref => $data) {
            if (!$data['id']) {
                continue;
            }
            $success = (int) @$data['success'];
            $errors  = (int) @$data['errors'];
            $total   = $success + $errors;

            $this->queries[] = sprintf(
                'update %s set num_pending = num_pending - %d, num_error = num_error + %d, num_complete = num_complete + %d where id = %s;',
                $ss->getTableName(), $total, $errors, $success, $data['id']
            );
        }
    }

    /**
     * replace refs with ids.
     */
    protected function replaceRefs()
    {
        /** @var SendmailSourceRepository $ss */
        $ss    = $this->getEntityManager()->getRepository('EmailBundle:SendmailSource');
        $found = [];
        foreach ($ss->getIdsByRefs(array_keys($this->refs)) as $row) {
            $found[$row['ref']] = 1;
            if (!@$this->refs[$row['ref']]) {
                continue;
            }

            $this->refs[$row['ref']]['id'] = $row['id'];
            foreach ($this->refs[$row['ref']]['keys'] as $key) {
                $this->params[$key] = $row['id'];
            }
        }

        /*
         * clear records with no found ids
         */
        if (count($this->refs) > count($found)) {
            $proceed = array_diff_key($this->refs, $found);

            foreach ($proceed as $ref => $data) {
                foreach ($data['keys'] as $k) {
                    unset($this->params[$k]); // unset $this->params[:ssidX]
                    $k = substr($k, 5); // remove ":ssid" part
                    unset(
                        $this->params[':email'.$k],
                        $this->params[':event'.$k],
                        $this->params[':info'.$k],
                        $this->params[':details'.$k],
                        $this->queries[$k]
                    );
                }
                unset($this->refs[$ref]);
            }
        }
    }

    /**
     * make inserts.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function flush()
    {
        if (!$this->queries) {
            return;
        }

        $this->replaceRefs();
        $this->queryCounts();

        if (!$this->queries) {
            return;
        }

        $conn = $this->getEntityManager()->getConnection();
        $conn->beginTransaction();

        $query         = implode("\n", $this->queries);
        $params        = $this->params;
        $this->queries = $this->params = $this->refs = [];

        try {
            $conn->executeQuery($query, $params);
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
