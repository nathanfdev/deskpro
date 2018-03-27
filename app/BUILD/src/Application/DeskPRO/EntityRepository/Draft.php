<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;

class Draft extends AbstractEntityRepository
{
    /**
     * @param $content_type
     * @param $content_id
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return \Application\DeskPRO\Entity\Draft|null
     */
    public function getDraft($content_type, $content_id, Entity\Person $person = null)
    {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        return $this->getEntityManager()->createQuery('
            SELECT d
            FROM DeskPRO:Draft d
            WHERE d.content_type = ?0
                AND d.content_id = ?1
                AND d.person = ?2
        ')->setParameters([$content_type, $content_id, $person])->getOneOrNullResult();
    }

    public function getActiveDrafts($content_type, $content_id, $update_offset = 600)
    {
        if (!$content_id) {
            return [];
        }

        if (!is_array($content_id)) {
            $single_set = $content_id;
            $content_id = [$content_id];
        } else {
            $single_set = false;
        }

        $drafts = $this->getEntityManager()->createQuery('
            SELECT d, p
            FROM DeskPRO:Draft d
            INNER JOIN d.person p
            WHERE d.content_type = ?0
                AND d.content_id IN (?1)
                AND d.date_created >= ?2
            ORDER BY d.date_created
        ')->execute([$content_type, $content_id, new \DateTime("-$update_offset seconds")]);

        $output = [];
        foreach ($drafts as $draft) {
            $output[$draft->content_id][$draft->person->getId()] = $draft;
        }

        if ($single_set) {
            return isset($output[$single_set]) ? $output[$single_set] : [];
        } else {
            return $output;
        }
    }

    /**
     * @param string                             $content_type
     * @param int                                $content_id
     * @param string                             $message
     * @param string                             $message_html
     * @param array                              $extras
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return \Application\DeskPRO\Entity\Draft
     */
    public function insertDraft($content_type, $content_id, $message, $message_html, array $extras = [], Entity\Person $person = null)
    {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        $draft = $this->getDraft($content_type, $content_id, $person);
        if (!$draft) {
            $draft = new \Application\DeskPRO\Entity\Draft();
        }

        $draft->date_created = new \DateTime();
        $draft->content_type = $content_type;
        $draft->content_id   = $content_id;
        $draft->message      = $message;
        $draft->message_html = $message_html;
        $draft->extras       = $extras;
        $draft->person       = $person;

        try {
            if ($draft->id) {
                $this->getEntityManager()->getConnection()->executeUpdate('
                    DELETE FROM drafts
                    WHERE content_type = ? AND content_id = ? AND person_id =? AND id != ?
                ', [
                    $content_type,
                    $content_id,
                    $person->getId(),
                    $draft->id,
                ]);
            } else {
                $this->getEntityManager()->getConnection()->executeUpdate('
                    DELETE FROM drafts
                    WHERE content_type = ? AND content_id = ? AND person_id =?
                ', [
                    $content_type,
                    $content_id,
                    $person->getId(),
                ]);
            }

            $this->getEntityManager()->persist($draft);
            $this->getEntityManager()->flush($draft);
        } catch (\PDOException $e) {
            return;
        }

        return $draft;
    }

    public function deleteDraft($content_type, $content_id, Entity\Person $person = null)
    {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        $draft = $this->getDraft($content_type, $content_id, $person);
        if ($draft) {
            App::getOrm()->remove($draft);
            App::getOrm()->flush();

            if ($content_type == 'ticket') {
                App::getContainer()
                    ->get('event_dispatcher')
                    ->dispatch(
                        TicketUpdatedEvent::EVENT_NAME,
                        new TicketUpdatedEvent(
                            'agent.ticket-draft-updated',
                            [
                                'ticket_id'  => $content_id,
                                'draft_html' => false,
                                'via_person' => $person->getId(),
                            ]

                        )
                    );
            }
        }
    }

    public function deleteDraftsForContent($content_type, $content_id)
    {
        App::getDb()->delete('drafts', [
            'content_type' => $content_type,
            'content_id'   => $content_id,
        ]);
    }
}
