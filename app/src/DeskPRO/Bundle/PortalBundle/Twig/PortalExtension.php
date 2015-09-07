<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\AppBundle\Helper\TicketPublicIdResolver;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PortalExtension extends \Twig_Extension
{
    /**
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Content\AvatarResolver
     */
    private $avatar_resolver;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TicketPublicIdResolver
     */
    private $ticket_public_id_resolver;

    /**
     * @param ContainerInterface $continer
     */
    public function __construct(ContainerInterface $continer)
    {
        $this->container = $continer;
        $this->brand_stack       = $continer->get('brand_stack');
        $this->settings_resolver = $continer->get('settings_resolver');
        $this->avatar_resolver   = $continer->get('avatar_resolver');
        $this->ticket_public_id_resolver = $continer->get('ticket.public_id_resolver');
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ticket_status', array($this, 'getTicketStatusString')),
            new \Twig_SimpleFunction('ticket_public_id', array($this, 'getPublicTicketId')),
            new \Twig_SimpleFunction('brand_setting', array($this, 'getBrandSetting'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('brand', array($this, 'getBrand')),
            new \Twig_SimpleFunction('avatar_url', array($this, 'getAvatarUrl')),
            new \Twig_SimpleFunction('render_message', array($this, 'getRenderedObject'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('render_news', array($this, 'getRenderedObject'), array('is_safe' => array('html')))
        );
    }

    public function getPublicTicketId($ticket)
    {
        if ($ticket instanceof TicketView) {
            $ticket = $ticket->ticket;
        }

        if (!$ticket instanceof Entity\Ticket) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Twig function "ticket_public_id" requires a Ticket or TicketView, but "" given',
                    is_object($ticket) ? get_class($ticket) : 'scalar'
                )
            );
        }

        return $this->ticket_public_id_resolver->findId($ticket);
    }

    /**
     * @param string $setting
     * @param mixed $default
     * @return mixed
     */
    public function getBrandSetting($setting, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($setting, $default);
    }

    /**
     * @param string $prop
     * @return string
     */
    public function getBrand($prop)
    {
        return $this->brand_stack->getActive()->getBrand()->get($prop);
    }

    /**
     * @param Entity\Ticket $ticket
     * @return string
     */
    public function getTicketStatusString($ticket)
    {
        switch ($ticket->status_code) {
            case Entity\Ticket::STATUS_RESOLVED:
                return 'Resolved';
            case Entity\Ticket::STATUS_AWAITING_AGENT:
                return 'Awaiting Agent';
            case Entity\Ticket::STATUS_AWAITING_USER:
                return 'Awaiting You';
            case Entity\Ticket::STATUS_HIDDEN:
                return 'Hidden';
            case Entity\Ticket::STATUS_ARCHIVED:
                return 'Archived';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get URL to a profile picture/avatar.
     *
     * @param mixed  $obj
     * @param int    $size
     *
     * @return string the url
     */
    public function getAvatarUrl($obj = null, $size = 80)
    {
        return $this->avatar_resolver->getAvatar($obj, $size);
    }

    /**
     * @param mixed $obj
     * @param mixed $opt
     * @return string
     */
    public function getRenderedObject($obj, $opt = null)
    {
        if ($obj instanceof Entity\TicketMessage) {
            return $this->container->get('ticket_message.renderer')->render($obj);
        } elseif ($obj instanceof Entity\News) {
            if ($opt == 'exceprt') {
                return $this->container->get('news.renderer')->renderExceprt($obj);
            } else {
                return $this->container->get('news.renderer')->render($obj);
            }
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return array('global_settings' => $this->settings_resolver->getGlobalSettings());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'portal_extension';
    }
}
