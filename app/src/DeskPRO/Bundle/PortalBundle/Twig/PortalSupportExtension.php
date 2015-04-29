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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extension designed to make working with templates easier by simplifying
 * expressions or making them look less scary.
 */
class PortalSupportExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $continer;

    /**
     * @param ContainerInterface $continer
     */
    function __construct(ContainerInterface $continer)
    {
        $this->continer = $continer;
    }


    /**
     * @return array
     */
    public function getFunctions()
    {
        $funcs = array(
            new \Twig_SimpleFunction('can_use_*', array($this, 'canUseCheck')),
            new \Twig_SimpleFunction('is_user', array($this, 'isUser')),
            new \Twig_SimpleFunction('is_guest', array($this, 'isGuest')),
        );

        return $funcs;
    }


    /**
     * Check if the current user can see/use a certain feature.
     *
     * @param string $name
     * @return bool
     */
    public function canUseCheck($name)
    {
        $n = strtoupper($name);
        return $this->continer->get('security.authorization_checker')->isGranted("USE_" . $n);
    }


    /**
     * @return bool
     */
    public function isUser()
    {
        return $this->continer->get('security.authorization_checker')->isGranted("ROLE_USER");
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return !$this->continer->get('security.authorization_checker')->isGranted("ROLE_USER");
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'portal_support_extension';
    }
}
