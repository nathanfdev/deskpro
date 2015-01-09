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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Twig;


use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsResolver;

class PortalExtension extends \Twig_Extension
{
    /**
     * @var \Application\DeskPRO\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settings_resolver;


    /**
     * @param BrandStack       $brand_stack
     * @param SettingsResolver $settings_resolver
     */
    public function __construct(BrandStack $brand_stack, SettingsResolver $settings_resolver)
    {
        $this->brand_stack = $brand_stack;
        $this->settings_resolver = $settings_resolver;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('person_picture_url', array($this, 'getPersonPictureUrl')),
            new \Twig_SimpleFunction('brand_setting', array($this, 'getBrandSetting'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('*', array($this, 'processPortalTag'), array('is_safe' => array('html'))),
        );
    }

    public function getBrandSetting($setting, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($setting, $default);
    }

    /**
     * Get URL to a persons profile picture
     *
     * Use this twig func instead of calling an entity directly in twig for urls
     *
     * @param Person $person
     * @param int $size
     * @param bool $secure
     *
     * @return string the url
     */
    public function getPersonPictureUrl(Person $person = null, $size = 80, $secure = false)
    {
        if ($person) {
            return $person->getPictureUrl($size, $secure);
        }
    }

    /**
     * @param string $tag_name
     * @param array  $arguments
     * @return string
     */
    public function processPortalTag($tag_name, $arguments = array())
    {
        return $this->brand_stack->getActive()->renderTag($tag_name, $arguments);
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
