<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

    protected $queries = array();

    protected $params = array();

    protected $refs = array();

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
        $found = array();
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
        $this->queries = $this->params = $this->refs = array();

        try {
            $conn->executeQuery($query, $params);
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
