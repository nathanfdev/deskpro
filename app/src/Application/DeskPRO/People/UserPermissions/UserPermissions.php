<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 */

namespace Application\DeskPRO\People\UserPermissions;

use Application\DeskPRO\People\UserPermissions\Value\ArticlePermissions;
use Application\DeskPRO\People\UserPermissions\Value\ChatPermissions;
use Application\DeskPRO\People\UserPermissions\Value\DownloadPermissions;
use Application\DeskPRO\People\UserPermissions\Value\FeedbackPermissions;
use Application\DeskPRO\People\UserPermissions\Value\NewsPermissions;
use Application\DeskPRO\People\UserPermissions\Value\TicketPermissions;

class UserPermissions
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

    public function __construct()
    {
        $this->ticket   = new TicketPermissions();
        $this->chat     = new ChatPermissions();
        $this->feedback = new FeedbackPermissions();
        $this->article  = new ArticlePermissions();
        $this->download = new DownloadPermissions();
        $this->news     = new NewsPermissions();
    }


    /**
     * @return array
     */
    public function toArray()
    {
        $arr = array();
        foreach ($this->getTypes() as $prop) {
            $arr[$prop] = array();
            foreach ($this->$prop->getNames() as $name) {
                $arr[$prop][$name] = (bool)$this->$prop->$name;
            }
        }

        return $arr;
    }


    /**
     * Reads perms in from an array
     *
     * @param array $perms
     */
    public function fromArray(array $perms)
    {
        foreach ($this->getTypes() as $prop) {
            if (!isset($perms[$prop])) continue;

            foreach ($this->$prop->getNames() as $name) {
                $this->$prop->$name = isset($perms[$prop][$name]) ? ((bool)$perms[$prop][$name]) : false;
            }
        }
    }


    /**
     * @return array
     */
    public function getTypes()
    {
        return array('ticket', 'chat', 'feedback', 'article', 'download', 'news');
    }
}
