<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\SnippetFormatter;

class SubjectAction extends AbstractAction
{
    /** @var string */
    protected $subject;

    public function __construct($subject)
    {
        if (!$subject) {
            $subject = '(untitled)';
        }
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $subject_text = $this->subject;

        $formatter    = new SnippetFormatter(App::getContainer()->get('twig'));
        $subject_text = $formatter->formatText($subject_text, $ticket);

        $ticket['subject'] = $subject_text;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            ['action' => 'subject', 'subject' => $this->subject],
        ];
    }

    /**
     * @return string
     */
    public function getSuject()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        if ($as_html) {
            $html = htmlspecialchars($this->subject, \ENT_QUOTES);
            $ret  = 'Set subject: <span class="with-set-subject">'.$html.'</span>';
        } else {
            $ret = 'Set subject: '.$this->subject;
        }

        return $ret;
    }
}
