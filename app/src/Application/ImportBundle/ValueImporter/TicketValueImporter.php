<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\RecordMapper\TicketStatusRecordMapper;
use Application\ImportBundle\Value\TicketValue;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

/**
 * Class TicketValueImporter
 * @package Application\ImportBundle\ValueImporter
 */
class TicketValueImporter extends AbstractValueImporter
{
    /**
     * @param mixed $tval
     *
     * @return bool
     * @throws BadDataException
     */
    public function importValue($tval)
    {
        if (!($tval instanceof TicketValue)) {
            throw new \InvalidArgumentException("This importer can only import TicketValue");
        }

        $log_id    = "Ticket :: " . $tval->oid . " ";
        $ticket_id = null;
        $record    = array();

        #------------------------------
        # Grab the person
        #------------------------------
        if ($tval->person && StringEmail::isValueValid($tval->person)) {
            $personId = $this->getMappers()->findIdFromMappedValue('person', $tval->person);

            if ($personId) {
                $this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $tval->person));
                $record['person_id'] = $personId;
            } else {
                $this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $tval->person));

                return false;
            }
        } else {
            throw new BadDataException("TicketValue must have a person specified");
        }

        if (!$tval->subject) {
            throw new BadDataException("TicketValue must have a subject specified");
        }

        $record['subject'] = $tval->subject;

        #------------------------------
        # Agent
        #------------------------------
        if ($tval->agent && $this->getMappers()->getMapper('person')->checkIsAgent($tval->agent)) {
            $personId = $this->getMappers()->findIdFromMappedValue('person', $tval->agent);

            if ($personId) {
                $this->getLogger()->info(sprintf("[%s] Found existing agent %s", $log_id, $tval->person));
                $record['agent_id'] = $personId;
            }
        }

        #------------------------------
        # Departments
        #------------------------------
        if ($tval->department) {
            $departmentId = $this->getMappers()->findIdFromMappedValue('department', $tval->department);
            if ($departmentId) {
                $this->getLogger()->info(sprintf("[%s] Found existing department %s", $log_id, $tval->department));
                $record['department_id'] = $departmentId;
            } else {
                $this->getLogger()->warning(sprintf("[%s] New Department found %s (creating)", $log_id, $tval->department));
                $this->getDb()->insert('departments', array(
                    'title'              => $tval->department,
                    'is_tickets_enabled' => 1
                ));

                $record['department_id'] = $this->getDb()->lastInsertId();
                $this->getMappers()->learnMapping('department', array('id' => $record['department_id'], 'title' => $tval->department));
            }
        }

        // Lang
        if ($tval->language) {
            $languageId = $this->getMappers()->findIdFromMappedValue('language', $tval->language);
            if ($languageId) {
                $this->getLogger()->info(sprintf("[%s] Found existing language %s", $log_id, $tval->language));
                $record['language_id'] = $languageId;
            } else {
                $this->getLogger()->notice(sprintf("[%s] Could not map language value: %s (skipping)", $log_id, $tval->language));
            }
        }

        // Categories
        if ($tval->category) {
            $categoryId = $this->getMappers()->findIdFromMappedValue('ticket_category', $tval->category);
            if ($categoryId) {
                $this->getLogger()->notice(sprintf("[%s] Found existing ticket category %s", $log_id, $tval->category));
                $record['category_id'] = $categoryId;
            } else {
                $this->getLogger()->notice(sprintf("[%s] Could not map ticket category value: %s (creating)", $log_id, $tval->category));
                $this->getDb()->insert('ticket_categories', array(
                        'title'			=> $tval->category
                    ));

                $record['category_id'] = $this->getDb()->lastInsertId();
                $this->getMappers()->learnMapping('ticket_category', array('id' => $record['category_id'], 'title' => $tval->category));
            }
        }

        // Priority
        if ($tval->priority) {
            $priorityId = $this->getMappers()->findIdFromMappedValue('ticket_priority', $tval->priority);
            if ($priorityId) {
                $this->getLogger()->notice(sprintf("[%s] Found existing ticket priority %s", $log_id, $tval->priority));
                $record['priority_id'] = $priorityId;
            } else {
                $this->getLogger()->notice(sprintf("[%s] Could not map ticket priority value: %s (creating)", $log_id, $tval->priority));
                $this->getDb()->insert('ticket_priorities', array(
                    'title' => $tval->priority,
                ));

                $record['priority_id'] = $this->getDb()->lastInsertId();
                $this->getMappers()->learnMapping('ticket_priority', array('id' => $record['priority_id'], 'title' => $tval->priority));
            }
        }

        //Ref
        if (!$tval->ref) {
            $record['ref'] = Strings::random(10, Strings::CHARS_ALPHANUM_IU);
        } else {
            $query = 'SELECT id FROM tickets WHERE ref = ?';
            $duplicateRef = $this->getDb()->fetchColumn($query, array($tval->ref));

            if ($duplicateRef) {
                $record['ref'] = Strings::random(10, Strings::CHARS_ALPHANUM_IU);
            } else {
                $record['ref'] = $tval->ref;
            }
        }

        //Status
        /** @var TicketStatusRecordMapper $ticket_status_mapper */
        $ticket_status_mapper = $this->getMappers()->getMapper('ticket_status');
        if ($tval->status && $ticket_status_mapper->isValidStatus($tval->status)) {
            $this->getLogger()->notice(sprintf("[%s] Found existing ticket status %s", $log_id, $tval->status));
            $record['status'] = $tval->status;
        }

        //Date fields
        $record['date_created']  = $tval->date_created ? $tval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $record['date_archived'] = $tval->date_archived ? $tval->date_archived->format('Y-m-d H:i:s') : null;
        $record['date_resolved'] = $tval->date_resolved ? $tval->date_resolved->format('Y-m-d H:i:s') : null;

        // Org
        if ($tval->organization) {
            $organizationId = $this->getMappers()->findIdFromMappedValue('organization', $tval->organization);
            if ($organizationId) {
                $this->getLogger()->notice(sprintf("[%s] Found existing organization \"%s\"", $log_id, $tval->organization));
                $record['organization_id'] = $organizationId;
            } else {
                $this->getLogger()->notice(sprintf("[%s] New organization %s", $log_id, $tval->organization));
                if ($this->isTestMode()) {
                    $record['organization_id'] = -1;
                } else {
                    $this->getDb()->insert('organizations', array(
                        'name'         => $tval->organization,
                        'date_created' => date('Y-m-d H:i:s')
                    ));
                    $record['organization_id'] = $this->getDb()->lastInsertId();
                }

                $this->getMappers()->learnMapping('organization', array('id' => $record['organization_id'], 'name' => $tval->organization));
            }
        }

        #------------------------------
        # Save data
        #------------------------------

        if (!$this->isTestMode()) {
            $this->getDb()->insert('tickets', $record);

            $ticket_id = $this->getDb()->lastInsertId();

            $this->getLogger()->info(sprintf("[%s] Created %d", $log_id, $ticket_id));
        }


        #------------------------------
        # Participants
        #------------------------------
        if ($ticket_id && $tval->participants) {
            $tval->participants = array_filter($tval->participants, function ($email) {
                return StringEmail::isValueValid($email);
            });

            if ($tval->participants) {
                foreach ($tval->participants as $participant) {
                    $participantId = $this->getMappers()->findIdFromMappedValue('person', $participant);

                    if ($participantId) {
                        $batch = array('ticket_id' => $ticket_id, 'person_id' => $participantId);
                        $this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $participant));
                        $this->getDb()->insert('tickets_participants', $batch);
                    }
                }
            }
        }

        #------------------------------
        # Ticket Messages
        #------------------------------

        if ($ticket_id && $tval->messages) {
            foreach ($tval->messages as $message) {
                $record = array();
                $messagePersonId = $this->getMappers()->findIdFromMappedValue('person', $message->person);

                if ($messagePersonId) {
                    $this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $tval->person));
                    $record['person_id'] = $messagePersonId;
                } else {
                    continue;
                }

                $record['ticket_id']    = $ticket_id;
                $record['message']      = $message->message_text ?: '';
                $record['date_created'] = $message->date_created ?  $message->date_created->format('Y-m-d H:i:s') : ($tval->date_created ? $tval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'));

                $this->getDb()->insert('tickets_messages', $record);

                $message_id = $this->getDb()->lastInsertId();

                foreach($message->attachments as $attachment) {
                    $attachment_value_importer = new AttachmentValueImporter(
                        $this->getMode(),
                        $this->getContainer(),
                        $this->getLogger(),
                        $this->getMappers()
                    );

                    $blob = $attachment_value_importer->importValue($attachment);
                    $ticket_attachment_record = array(
                        'ticket_id'  => $ticket_id,
                        'person_id'  => $messagePersonId,
                        'blob_id'    => $blob['id'],
                        'message_id' => $message_id
                    );

                    $this->getDb()->insert('tickets_attachments', $ticket_attachment_record);
                }
            }
        }

        #------------------------------
        # Labels
        #------------------------------
        if ($ticket_id && $tval->labels) {
            $batch = array_map(function ($l) use ($ticket_id) {
                return array(
                    'ticket_id' => $ticket_id,
                    'label'     => $l
                );
            }, $tval->labels);

            $this->getDb()->batchInsert('labels_tickets', $batch, true);
        }

        #------------------------------
        # Custom Fields
        #------------------------------
        if ($ticket_id && !empty($tval->custom_def)) {
            $custom_field_importer = new CustomDefTicketValueImporter(
                $this->getMode(),
                $this->getContainer(),
                $this->getLogger(),
                $this->getMappers(),
                $ticket_id
            );

            foreach ($tval->custom_def as $custom_value) {
                $custom_field_importer->importValue($custom_value);
            }
        }
    }
}
