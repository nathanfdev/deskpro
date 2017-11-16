<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\SnippetFormatter;

/**
 * Class ReplyAction.
 */
class ReplyAction extends AbstractReplyAction
{
    /**
     * @var string
     */
    protected $reply_text;

    /**
     * @var array
     */
    protected $attach_ids = [];

    /**
     * @var bool
     */
    protected $is_html = false;

    /**
     * @var bool
     */
    protected $is_note = false;

    /**
     * @var int|null
     */
    protected $person_id = null;

    /**
     * Constructor.
     *
     * @param string $reply_text
     * @param array  $attach_ids
     * @param null   $reply_pos
     * @param bool   $is_html
     * @param bool   $is_note
     * @param null   $person_id
     */
    public function __construct($reply_text, array $attach_ids = [], $reply_pos = null, $is_html = false, $is_note = false, $person_id = null)
    {
        $this->reply_text = $reply_text;
        $this->attach_ids = $attach_ids;
        $this->reply_pos  = $reply_pos;
        $this->is_html    = $is_html;
        $this->is_note    = $is_note;
        $this->person_id  = $person_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if (!$person->PermissionsManager->TicketChecker->canReply($ticket, 'reply')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $person = $this->getTicketPerson($ticket);
        if (!$person) {
            return;
        }

        $message = new TicketMessage();
        $message->setPerson($person);
        $message->setAsAgentNote($this->is_note);
        $message->setDateCreated(new \DateTime('+1 second'));
        $message->setMessageHtml($this->getMessageContent($ticket));

        $ticket->addMessage($message);

        if ($this->attach_ids) {
            foreach ($this->attach_ids as $blob_id) {
                $blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

                if ($blob) {
                    $attach           = new TicketAttachment();
                    $attach['blob']   = $blob;
                    $attach['person'] = $this->person_context;

                    $message->addAttachment($attach);
                    App::getOrm()->persist($attach);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            [
                'action'     => 'reply',
                'reply_text' => $this->reply_text,
                'attach_ids' => $this->attach_ids,
                'is_html'    => $this->is_html,
                'is_note'    => $this->is_note,
                'person_id'  => $this->person_id,
            ],
        ];
    }

    /**
     * Get reply text.
     *
     * @return int
     */
    public function getReplyText()
    {
        return $this->reply_text;
    }

    /**
     * Get attach ids.
     *
     * @return array
     */
    public function getAttachIds()
    {
        return $this->attach_ids;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $other_action)
    {
        return $other_action;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        if ($as_html) {
            $flat = str_replace(["\r\n", "\n"], ' ', $this->reply_text);
            if (strlen($flat) > 80) {
                $flat = substr($flat, 0, 80).'...';
            }

            $desc = '<span class="highlight-description">'.htmlspecialchars($flat).'</span>';

            if (isset($_GET['macro_reply_context'])) {
                if ($this->reply_pos == self::REPLY_POS_OVERWRITE) {
                    $ret = 'Set reply text';
                } elseif ($this->reply_pos == self::REPLY_POS_APPEND) {
                    $ret = 'Append reply text';
                } else {
                    $ret = 'Prepend reply text';
                }

                if ($this->is_html) {
                    $html = $this->reply_text;
                } else {
                    $html = '<p>'.nl2br(htmlspecialchars(trim($this->reply_text), \ENT_QUOTES)).'</p>';
                }

                $ret = '<span class="with-reply" data-reply-pos="'.$this->reply_pos.'">'.$ret.'<script type="text/x-deskpro-plain" class="reply-text">'.$html.'</script></span>';

                return $ret;
            }

            return $this->is_note
                ? $tr->phrase('agent.tickets.add_note_x_action', ['desc' => $desc])
                : $tr->phrase('agent.tickets.add_reply_x_action', ['desc' => $desc]);
        }

        return $this->is_note
            ? $tr->phrase('agent.tickets.add_note_action')
            : $tr->phrase('agent.tickets.add_reply_action');
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketPerson(Ticket $ticket)
    {
        $person = null;

        if ($this->person_id) {
            $person = App::getDataService('Agent')->get($this->person_id);
        }
        if (!$person) {
            $person = parent::getTicketPerson($ticket);
        }

        return $person;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageContent(Ticket $ticket)
    {
        $formatter = new SnippetFormatter(App::getContainer()->get('twig'));

        $replyText = $this->reply_text;
        $replyText = $formatter->formatText($replyText, $ticket);

        return $replyText;
    }
}
