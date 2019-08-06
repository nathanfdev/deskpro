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
use Application\DeskPRO\People\UserPermissions\Value\CommunityPermissions;
use Application\DeskPRO\People\UserPermissions\Value\DownloadPermissions;
use Application\DeskPRO\People\UserPermissions\Value\GuidesPermissions;
use Application\DeskPRO\People\UserPermissions\Value\NewsPermissions;
use Application\DeskPRO\People\UserPermissions\Value\TicketPermissions;
use Symfony\Component\DependencyInjection\Container;

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
     * @var \Application\DeskPRO\People\UserPermissions\Value\CommunityPermissions
     */
    public $community;

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
        'community' => 'community',
        'articles'  => 'article',
        'downloads' => 'download',
        'news'      => 'news',
        'guides'    => 'guide',
    ];

    public function __construct()
    {
        $this->ticket    = new TicketPermissions();
        $this->chat      = new ChatPermissions();
        $this->community = new CommunityPermissions();
        $this->article   = new ArticlePermissions();
        $this->download  = new DownloadPermissions();
        $this->news      = new NewsPermissions();
        $this->guide     = new GuidesPermissions();
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($name)
    {
        if (!$name) {
            return false;
        }

        list($prop, $name) = explode('.', $name, 2);
        if (!$prop || !$name) {
            return false;
        }

        if (isset(self::$prefix_map[$prop])) {
            $prop = self::$prefix_map[$prop];
        } else {
            if (!property_exists($this, $prop)) {
                return false;
            }
        }

        $getter = 'get'.Container::camelize($name);

        return method_exists($this->$prop, $getter)
            ? $this->$prop->$getter()
            : (property_exists($this->$prop, $name) ? (bool) $this->$prop->$name : false);
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
                $getter            = 'get'.Container::camelize($name);
                $arr[$prop][$name] = method_exists($this->$prop, $getter)
                    ? $this->$prop->$getter()
                    : (bool) $this->$prop->$name;
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
                $setter = 'set'.Container::camelize($name);
                if (method_exists($this->$prop, $setter)) {
                    $this->$prop->$setter(isset($perms[$prop][$name]) ? $perms[$prop][$name] : false);
                } else {
                    $this->$prop->$name = isset($perms[$prop][$name]) ? ((bool) $perms[$prop][$name]) : false;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return ['ticket', 'chat', 'community', 'article', 'download', 'news', 'guide'];
    }
}
