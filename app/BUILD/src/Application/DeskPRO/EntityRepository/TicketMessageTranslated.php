<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\TicketMessage as TicketMessageEntity;

class TicketMessageTranslated extends AbstractEntityRepository
{
    /**
     * Finds all translated messages on a message.
     *
     * @param TicketMessageEntity  $ticket_message
     * @param string|string[]|null $lang           Optionally only fetch these lang codes
     *
     * @return array|TicketMessageEntity|null
     */
    public function getForMessage(TicketMessageEntity $ticket_message, $lang_code = null)
    {
        if ($lang_code) {
            if (!is_array($lang_code)) {
                $lang_code = [$lang_code];
            }

            // Also get generic ones. eg if we specified en_US but there might be ones as 'en'
            foreach (array_values($lang_code) as $c) {
                if (strpos($c, '_')) {
                    list($x)     = explode('_', $c, 2);
                    $lang_code[] = $x;
                }
            }

            $lang_code = array_unique($lang_code);

            $got = $this->_em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessageTranslated m INDEX BY m.lang_code
                WHERE m.ticket_message = ?0 AND m.lang_code IN (?1)
            ')->setParameters([$ticket_message, array_values($lang_code)])->execute();

            if (!$got) {
                return;
            }

            foreach ($lang_code as $c) {
                if (isset($got[$c])) {
                    return $got[$c];
                }
            }

            return;
        } else {
            return $this->_em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessageTranslated m INDEX BY m.lang_code
                WHERE m.ticket_message = ?0
            ')->setParameters([$ticket_message])->getOneOrNullResult();
        }
    }

    /**
     * Get all translated messages for a collection of messages.
     *
     * @param TicketMessageEntity[] $ticket_messages
     * @param string[]|string|null  $lang_code       Optionally only these lang codes (if using $single, then in order of importance)
     * @param single                $single          Only return a single translation per message
     *
     * @return array
     */
    public function getForMessages(array $ticket_messages, $lang_code = null, $single = true)
    {
        if ($lang_code) {
            if (!is_array($lang_code)) {
                $lang_code = [$lang_code];
            }

            // Also get generic ones. eg if we specified en_US but there might be ones as 'en'
            foreach (array_values($lang_code) as $c) {
                if (strpos($c, '_')) {
                    list($x)     = explode('_', $c, 2);
                    $lang_code[] = $x;
                }
            }

            $lang_code = array_unique($lang_code);

            if (!$ticket_messages || !$lang_code) {
                return [];
            }

            $trans_messages = $this->_em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessageTranslated m
                WHERE m.ticket_message IN (?0) AND m.lang_code IN (?1)
            ')->setParameters([array_values($ticket_messages), array_values($lang_code)])->execute();

            $ret = [];

            foreach ($trans_messages as $msg) {
                $message_id = $msg->ticket_message->getId();
                $lang       = $msg->lang_code;

                if (!isset($ret[$message_id])) {
                    $ret[$message_id] = [];
                }

                $ret[$message_id][$lang] = $msg;
            }

            if ($lang_code && $single) {
                $ret_all = $ret;
                $ret     = [];

                foreach ($ret_all as $message_id => $langs) {
                    foreach ($lang_code as $c) {
                        if (isset($langs[$c])) {
                            $ret[$message_id] = $langs[$c];
                        }
                    }
                }
            }

            return $ret;
        } else {
            $trans_messages_x = $this->_em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessageTranslated m
                WHERE m.ticket_message IN (?0)
            ')->setParameters([$ticket_messages])->getOneOrNullResult();
            $trans_messages = [];
            foreach ($trans_messages_x as $tr) {
                $trans_messages[$tr->ticket_message->getId()] = $tr;
            }

            return $trans_messages;
        }
    }
}
