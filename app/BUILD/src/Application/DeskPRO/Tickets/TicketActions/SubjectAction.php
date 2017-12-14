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
