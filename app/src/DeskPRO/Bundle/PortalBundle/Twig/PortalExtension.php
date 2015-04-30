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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeView;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PortalExtension extends \Twig_Extension
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Brand\BrandStack
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
     * @param ContainerInterface $continer
     */
    public function __construct(ContainerInterface $continer)
    {
        $this->brand_stack       = $continer->get('brand_stack');
        $this->settings_resolver = $continer->get('settings_resolver');
        $this->avatar_resolver   = $continer->get('avatar_resolver');
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ticket_status', array($this, 'getTicketStatusString')),
            new \Twig_SimpleFunction('brand_setting', array($this, 'getBrandSetting'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('avatar_url', array($this, 'getAvatarUrl')),
        );
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
     * @param Ticket $ticket
     * @return string
     */
    public function getTicketStatusString(Ticket $ticket)
    {
        switch ($ticket->getStatusCode()) {
            case Ticket::STATUS_RESOLVED:
                return 'Resolved';
            case Ticket::STATUS_AWAITING_AGENT:
                return 'Awaiting Agent';
            case Ticket::STATUS_AWAITING_USER:
                return 'Awaiting You';
            case Ticket::STATUS_HIDDEN:
                return 'Hidden';
            case Ticket::STATUS_ARCHIVED:
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
        return $this->avatar_resolver->getAvatar($obj);
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
