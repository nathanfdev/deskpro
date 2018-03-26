<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\UserPermissions;

use Application\DeskPRO\People\PermissionsSetInterface;
use Application\DeskPRO\People\UserPermissions\Value\ArticlePermissions;
use Application\DeskPRO\People\UserPermissions\Value\ChatPermissions;
use Application\DeskPRO\People\UserPermissions\Value\DownloadPermissions;
use Application\DeskPRO\People\UserPermissions\Value\FeedbackPermissions;
use Application\DeskPRO\People\UserPermissions\Value\GuidesPermissions;
use Application\DeskPRO\People\UserPermissions\Value\NewsPermissions;
use Application\DeskPRO\People\UserPermissions\Value\TicketPermissions;

class UserPermissions implements PermissionsSetInterface
{
    /**
     * @var \Application\DeskPRO\People\UserPermissions\Value\TicketPermissions
     */
    public $ticket;

    /**
     * @var \Application\DeskPRO\People\UserPermissions\Value\ChatPermissions
     */
    public $chat;

    /**
     * @var \Application\DeskPRO\People\UserPermissions\Value\FeedbackPermissions
     */
    public $feedback;

    /**
     * @var \Application\DeskPRO\People\UserPermissions\Value\ArticlePermissions
     */
    public $article;

    /**
     * @var \Application\DeskPRO\People\UserPermissions\Value\DownloadPermissions
     */
    public $download;

    /**
     * @var \Application\DeskPRO\People\UserPermissions\Value\NewsPermissions
     */
    public $news;

    /**
     * @var \Application\DeskPRO\People\UserPermissions\Value\GuidesPermissions
     */
    public $guide;

    /**
     * @var array
     */
    public static $prefix_map = [
        'tickets'   => 'ticket',
        'chat'      => 'chat',
        'feedback'  => 'feedback',
        'articles'  => 'article',
        'downloads' => 'download',
        'news'      => 'news',
        'guides'    => 'guide',
    ];

    public function __construct()
    {
        $this->ticket   = new TicketPermissions();
        $this->chat     = new ChatPermissions();
        $this->feedback = new FeedbackPermissions();
        $this->article  = new ArticlePermissions();
        $this->download = new DownloadPermissions();
        $this->news     = new NewsPermissions();
        $this->guide    = new GuidesPermissions();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $arr = [];
        foreach ($this->getTypes() as $prop) {
            $arr[$prop] = [];
            foreach ($this->$prop->getNames() as $name) {
                $arr[$prop][$name] = (bool) $this->$prop->$name;
            }
        }

        return $arr;
    }

    /**
     * Reads perms in from an array.
     *
     * @param array $perms
     */
    public function fromArray(array $perms)
    {
        foreach ($this->getTypes() as $prop) {
            if (!isset($perms[$prop])) {
                continue;
            }

            foreach ($this->$prop->getNames() as $name) {
                $this->$prop->$name = isset($perms[$prop][$name]) ? ((bool) $perms[$prop][$name]) : false;
            }
        }
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return ['ticket', 'chat', 'feedback', 'article', 'download', 'news', 'guide'];
    }
}
