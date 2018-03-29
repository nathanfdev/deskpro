<?php

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketObjectUseLog;
use Application\DeskPRO\Tickets\SnippetFormatter;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetUseLog;

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
        if (App::$container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
            $item->snippet = App::getOrm()->find(Snippet::class, $snippet_id);
        } else {
            $item->snippet = App::getOrm()->find(TextSnippet::class, $snippet_id);
        }

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
            $t = $item->snippet ? $item->snippet->getTitle() : null;
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
            $id = $item->snippet ? $item->snippet->getId() : null;
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
    public function merge(ActionInterface $otherAction)
    {
        foreach ($otherAction->getSnippetItems() as $item) {
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
        $newSnippets = App::$container->get('deskpro.feature_flags')->hasBeta('new_snippets');

        // Hack ot append the proper position
        // when being viewed from replybox
        // See TicketController::ajaxGetMacroAction
        if (isset($_GET['macro_reply_context'])) {
            $ret      = [];
            $replyPos = null;

            foreach ($this->snippet_items as $item) {
                $replyPos    = $item->reply_pos;
                $snippetDesc = '';

                if ($item->reply_pos == 'overwrite') {
                    $snippetDesc = 'Reply with snippet: '.$item->snippet->getTitle();
                } elseif ($item->reply_pos == 'append') {
                    $snippetDesc = 'Append snippet to reply: '.$item->snippet->getTitle();
                } else {
                    $snippetDesc = 'Prepend snippet to reply: '.$item->snippet->getTitle();
                }

                $html        = '';
                $snippetText = [];
                if (!empty($GLOBALS['DP_ACTIVE_TICKET'])) {
                    /** @var Ticket $ticket */
                    $ticket = $GLOBALS['DP_ACTIVE_TICKET'];
                    $person = $ticket->getPerson();
                    if ($newSnippets) {
                        /** @var Snippet $snippet */
                        $snippet           = $item->snippet;
                        $ticketTranslation = null;
                        $personTranslation = null;
                        $agentTranslation  = null;
                        foreach ($snippet->getTranslations() as $translation) {
                            if ($translation->getLanguage() === $ticket->language) {
                                $ticketTranslation = $translation;
                            }
                            if ($person && $translation->getLanguage() === $person->getLanguage()) {
                                $personTranslation = $translation;
                            }
                            if ($ticket->getAgent() && $translation->getLanguage() === $ticket->getAgent()->getLanguage()) {
                                $agentTranslation = $translation;
                            }
                        }
                        $text = null;
                        if ($ticketTranslation && $ticketTranslation->getContent()) {
                            $text = $ticketTranslation->getContent();
                        } elseif ($personTranslation && $personTranslation->getContent()) {
                            $text = $personTranslation->getContent();
                        } elseif ($agentTranslation && $agentTranslation->getContent()) {
                            $text = $agentTranslation->getContent();
                        }
                        if ($text = trim($text)) {
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

                            $snippetText = implode("\n<br/><br/>\n", $snippetText);
                            $formatter   = new SnippetFormatter(App::getContainer()->get('twig'));
                            $formatter->addVar('agent_signature', '');
                            $html = $formatter->formatText($snippetText, $ticket);
                        }
                    } else {
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
                                    $snippetText[] = $text;
                                    break;
                                case self::REPLY_POS_PREPEND:
                                    array_unshift($snippetText, $text);
                                    break;
                                case self::REPLY_POS_OVERWRITE:
                                    $snippetText = [$text];
                                    break;
                            }

                            $snippetText = implode("\n<br/><br/>\n", $snippetText);
                            $formatter   = new SnippetFormatter(App::getContainer()->get('twig'));
                            $formatter->addVar('agent_signature', '');
                            $html = $formatter->formatText($snippetText, $ticket);
                        }
                    }
                }

                $snippetDesc = '<span class="with-reply" data-reply-pos="'.$replyPos.'">'.$snippetDesc;
                $snippetDesc .= '<script type="text/x-deskpro-plain" class="reply-text">'.$html.'</script></span>';

                $ret[] = $snippetDesc;
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
        $newSnippets = App::$container->get('deskpro.feature_flags')->hasBeta('new_snippets');

        $snippetText = [];
        foreach ($this->snippet_items as $item) {
            if ($newSnippets) {
                /** @var Snippet $snippet */
                $snippet            = $item->snippet;
                $ticketTranslation  = null;
                $personTranslation  = null;
                $snippetTranslation = null;
                $agentTranslation   = null;
                foreach ($snippet->getTranslations() as $translation) {
                    if ($translation->getLanguage() === $ticket->getLanguage()) {
                        $ticketTranslation = $translation;
                    }
                    if ($translation->getLanguage() === $person->getRealLanguage()) {
                        $personTranslation = $translation;
                    }
                    if ($ticket->getAgent() && $translation->getLanguage() === $ticket->getAgent()->getLanguage()) {
                        $agentTranslation = $translation;
                    }
                }
                $text = null;
                if ($ticketTranslation && $ticketTranslation->getContent()) {
                    $text               = $ticketTranslation->getContent();
                    $snippetTranslation = $ticketTranslation;
                } elseif ($personTranslation && $personTranslation->getContent()) {
                    $text               = $personTranslation->getContent();
                    $snippetTranslation = $personTranslation;
                } elseif ($agentTranslation && $agentTranslation->getContent()) {
                    $text               = $agentTranslation->getContent();
                    $snippetTranslation = $agentTranslation;
                }
                $text = trim($text);
                if (!$text) {
                    continue;
                }
                if ($snippetTranslation) {
                    $messages   = $ticket->getMessages();
                    $message    = $messages->last();
                    $snippetLog = SnippetUseLog::createSnippetTicketLog($message, $person, $snippetTranslation);
                    App::$container->getEm()->persist($snippetLog);
                }
            } else {
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
     * @var TextSnippet|Snippet
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
