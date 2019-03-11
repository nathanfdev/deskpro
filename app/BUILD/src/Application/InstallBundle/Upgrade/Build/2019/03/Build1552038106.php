<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Orb\Types\JsonObjectSerializer;

class Build1552038106 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->addPending('ticket_filters');
        $this->addPending('ticket_escalations');
        $this->addPendingToTriggers();
    }

    protected function addPending($table)
    {
        $rows = $this->getDbConnection()->fetchAll(sprintf(
            'SELECT id, terms FROM %s WHERE terms LIKE \'%%awaiting_agent%%\'',
            $table
        ));

        foreach ($rows as $row) {
            $terms   = json_decode($row['terms'], true);
            $changed = false;

            foreach ($terms as &$term) {
                if ($term['type'] == 'status'
                    && array_key_exists('status', $term['options'])
                ) {
                    if (is_array($term['options']['status'])) {
                        if (in_array('awaiting_agent', $term['options']['status'])
                            && !in_array('pending', $term['options']['status']) // if for some reason migration executed several times
                        ) {
                            $term['options']['status'][] = 'pending';
                            $changed                     = true;
                        }
                    } elseif ($term['options']['status'] == 'awaiting_agent') {
                        $term['options']['status'] = ['awaiting_agent', 'pending'];
                        $changed                   = true;
                    }
                }
            }

            if ($changed) {
                $statement = $this->getDbConnection('default')->prepare(sprintf(
                    'UPDATE %s SET terms = :terms WHERE id = :id',
                    $table
                ));
                $statement->execute([
                    'id'    => $row['id'],
                    'terms' => json_encode($terms),
                ]);
            }
        }
    }

    protected function addPendingToTriggers()
    {
        $rows = $this->getDbConnection()->fetchAll(
            'SELECT id, terms FROM ticket_triggers WHERE terms LIKE \'%%awaiting_agent%%\''
        );

        foreach ($rows as $row) {
            $changed = false;

            try {
                /** @var TriggerTerms $triggerTerms */
                $triggerTerms = JsonObjectSerializer::unserialize($row['terms']);
            } catch (\Exception $e) {
                $this->out(sprintf(
                    'Unserialize exception for Trigger #%s.',
                    $row['id']
                ));
                $triggerTerms = null;
            }

            if (!$triggerTerms) {
                continue;
            }

            $termsArray = $triggerTerms->getTerms();
            if (!$termsArray) {
                continue;
            }

            foreach ($termsArray as &$termArray) {
                if (array_key_exists('set_terms', $termArray)) {
                    foreach ($termArray['set_terms'] as &$setTermArray) {
                        $this->addPendingToTriggerTermArray($setTermArray, $changed);
                    }
                } else {
                    $this->addPendingToTriggerTermArray($termArray, $changed);
                }
            }

            if ($changed) {
                $triggerTermsUpdated = new TriggerTerms();
                foreach ($termsArray as $termArray) {
                    $triggerTermsUpdated->addTermFromArray($termArray);
                }

                if ($triggerTerms->count() !== $triggerTermsUpdated->count()) {
                    $this->out(sprintf(
                        "Can't update Trigger #%s. Updated terms count and initial terms count don't match",
                        $row['id']
                    ));

                    continue;
                }

                $statement = $this->getDbConnection('default')->prepare(
                    'UPDATE ticket_triggers SET terms = :terms WHERE id = :id'
                );
                $statement->execute([
                    'id'    => $row['id'],
                    'terms' => JsonObjectSerializer::serialize($triggerTermsUpdated),
                ]);
            }
        }
    }

    protected function addPendingToTriggerTermArray(&$termArray, &$changed)
    {
        if (
            is_array($termArray)
            && array_key_exists('type', $termArray)
            && $termArray['type'] == 'CheckStatus'
            && array_key_exists('options', $termArray)
            && array_key_exists('status', $termArray['options'])
        ) {
            $status = $termArray['options']['status'];
            if (is_array($status)) {
                if (in_array('awaiting_agent', $status)
                    && !in_array('pending', $status) // if for some reason migration executed several times
                ) {
                    $status[] = 'pending';
                    $changed  = true;
                }
            } elseif ($status == 'awaiting_agent') {
                $status  = ['awaiting_agent', 'pending'];
                $changed = true;
            }
            $termArray['options']['status'] = $status;
        }
    }
}
