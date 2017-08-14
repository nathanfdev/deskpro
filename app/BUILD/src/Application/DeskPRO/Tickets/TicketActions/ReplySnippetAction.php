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
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketObjectUseLog;
use Application\DeskPRO\Tickets\SnippetFormatter;

/**
 * Class ReplySnippetAction.
 */
class ReplySnippetAction extends AbstractReplyAction
{
    /**
     * @var ReplySnippetActionItem[]
     */
    protected $snippet_items;

    /**
     * Constructor.
     *
     * @param null $snippet_id
     * @param null $reply_pos
     */
    public function __construct($snippet_id = null, $reply_pos = null)
    {
        if (!$snippet_id) {
            return;
        }

        $item             = new ReplySnippetActionItem();
        $item->snippet_id = $snippet_id;
        $item->reply_pos  = $reply_pos;
        $item->snippet    = App::getOrm()->find('DeskPRO:TextSnippet', $snippet_id);

        if (!$item->snippet) {
            return;
        }

        if ($item->snippet) {
            $this->addSnippetItem($item);
        }

        $this->reply_pos = $reply_pos;
    }

    /**
     * @param ReplySnippetActionItem $item
     */
    public function addSnippetItem(ReplySnippetActionItem $item)
    {
        $this->snippet_items[] = $item;
    }

    /**
     * @return ReplySnippetActionItem[]
     */
    public function getSnippetItems()
    {
        return $this->snippet_items;
    }

    /**
     * @return string[]
     */
    public function getSnippetTitles()
    {
        $titles = [];
        foreach ($this->snippet_items as $item) {
            $t = $item->snippet ? $item->snippet->title : null;
            if ($t) {
                $titles[] = $t;
            }
        }

        return $titles;
    }

    /**
     * @return int[]
     */
    public function getSnippetIds()
    {
        $ids = [];
        foreach ($this->snippet_items as $item) {
            $id = $item->snippet ? $item->snippet->id : null;
            if ($id) {
                $ids[] = $id;
            }
        }

        return $ids;
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
        if (!$person->PermissionsManager->TicketChecker->canReply($ticket)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $content = $this->getMessageContent($ticket);
        if (!$content) {
            return;
        }

        $message = new TicketMessage();
        $message->setPerson($this->person_context);
        $message->setMessageHtml($content);

        $ticket->addMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if (!$this->snippet_items) {
            return [];
        }

        return [
            ['action' => 'reply_snippet'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $other_action)
    {
        foreach ($other_action->getSnippetItems() as $item) {
            $this->addSnippetItem($item);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        if (!$this->snippet_items) {
            return '<error>No snippet</error>';
        }

        // Hack ot append the proper position
        // when being viewed from replybox
        // See TicketController::ajaxGetMacroAction
        if (isset($_GET['macro_reply_context'])) {
            $ret       = [];
            $reply_pos = null;

            foreach ($this->snippet_items as $item) {
                $reply_pos    = $item->reply_pos;
                $snippet_desc = '';

                if ($item->reply_pos == 'overwrite') {
                    $snippet_desc = 'Reply with snippet: '.$item->snippet->title;
                } elseif ($item->reply_pos == 'append') {
                    $snippet_desc = 'Append snippet to reply: '.$item->snippet->title;
                } else {
                    $snippet_desc = 'Prepend snippet to reply: '.$item->snippet->title;
                }

                $html = '';
                if (!empty($GLOBALS['DP_ACTIVE_TICKET'])) {
                    $snippet_text = [];

                    $text = App::getTranslator()->objectChoosePhraseText(
                        $item->snippet,
                        'snippet',
                        [
                            $GLOBALS['DP_ACTIVE_TICKET']->language,
                        ]
                    );

                    if ($text = trim($text)) {
                        switch ($item->reply_pos) {
                            case self::REPLY_POS_APPEND:
                                $snippet_text[] = $text;
                                break;
                            case self::REPLY_POS_PREPEND:
                                array_unshift($snippet_text, $text);
                                break;
                            case self::REPLY_POS_OVERWRITE:
                                $snippet_text = [$text];
                                break;
                        }

                        $snippet_text = implode("\n<br/><br/>\n", $snippet_text);
                        $formatter    = new SnippetFormatter(App::getContainer()->get('twig'));
                        $formatter->addVar('agent_signature', '');
                        $html = $formatter->formatText($snippet_text, $GLOBALS['DP_ACTIVE_TICKET']);
                    }
                }

                $snippet_desc = '<span class="with-reply" data-reply-pos="'.$reply_pos.'">'.$snippet_desc;
                $snippet_desc .= '<script type="text/x-deskpro-plain" class="reply-text">'.$html.'</script></span>';

                $ret[] = $snippet_desc;
            }

            $ret = implode(', ', $ret);

            return $ret;
        }

        return 'Reply with snippet: '.implode(', ', $this->getSnippetTitles());
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageContent(Ticket $ticket)
    {
        $person = $this->getTicketPerson($ticket);
        if (!$person) {
            return;
        }

        if (!$this->snippet_items) {
            return;
        }

        $snippetText = [];
        foreach ($this->snippet_items as $item) {
            $text = App::getTranslator()->objectChoosePhraseText(
                $item->snippet,
                'snippet',
                [
                    $ticket->getLanguage(),
                    $person->getRealLanguage(),
                ]
            );
            $text = trim($text);
            if (!$text) {
                continue;
            }

            if ($item->snippet && $person) {
                $snippetLog = TicketObjectUseLog::createSnippetLog($ticket, $person, $item->snippet);
                App::$container->getEm()->persist($snippetLog);
            }

            switch ($item->reply_pos) {
                case self::REPLY_POS_APPEND:
                    $snippetText[] = $text;
                    break;
                case self::REPLY_POS_PREPEND:
                    array_unshift($snippetText, $text);
                    break;
                case self::REPLY_POS_OVERWRITE:
                    $snippetText = [$text];
                    break;
            }
        }

        $person->loadHelper('Agent');

        $formatter = new SnippetFormatter(App::getContainer()->get('twig'));
        $formatter->addVar('agent_signature', $person->getSignatureHtml());

        $snippetText = implode("\n<br/><br/>\n", $snippetText);
        $snippetText = $formatter->formatText($snippetText, $ticket);

        return $snippetText;
    }
}

class ReplySnippetActionItem
{
    /**
     * @var \Application\DeskPRO\Entity\TextSnippet
     */
    public $snippet;

    /**
     * @var int
     */
    public $snippet_id;

    /**
     * Possible values: append, prepend, overwrite.
     *
     * @var string
     */
    public $reply_pos;
}
