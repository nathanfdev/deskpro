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

use Application\ImportBundle\Entity\Person;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeView;
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
     * @var \DeskPRO\Bundle\AppBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @param ContainerInterface $continer
     */
    function __construct(ContainerInterface $continer)
    {
        $this->continer = $continer;
        $this->brand_stack = $continer->get('brand_stack');
    }

    public function getTokenParsers()
    {
        $token_parsers = array(
            new TokenParser\Show($this)
        );

        return $token_parsers;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        $funcs = array(
            new \Twig_SimpleFunction('can_use_*', array($this, 'canUseCheck')),
            new \Twig_SimpleFunction('has_any_*', array($this, 'hasAnyCheck')),
            new \Twig_SimpleFunction('is_user', array($this, 'isUser')),
            new \Twig_SimpleFunction('is_guest', array($this, 'isGuest')),
            new \Twig_SimpleFunction('col_count', array($this, 'countTruthy')),
            new \Twig_SimpleFunction('date', array($this, 'date')),

            new \Twig_SimpleFunction('this_*', array($this, 'processPortalPageTag'), array('is_safe' => array('html'), 'needs_context' => true)),
            new \Twig_SimpleFunction('*', array($this, 'processPortalTag'), array('is_safe' => array('html'))),
        );

        return $funcs;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('date', array($this, 'date')),
        );

        return $filters;
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
     * Check if there is any content to show for: articles, news, downloads, feedback
     *
     * @param string $name
     * @return bool
     */
    public function hasAnyCheck($name)
    {
        $sec = $this->continer->get('security.authorization_checker');

        switch ($name) {
            case 'articles':
                if ($sec->isGranted('USE_ARTICLES') && $this->continer->get('data.articles')->hasAny()) {
                    return true;
                }
                break;
            case 'news':
                if ($sec->isGranted('USE_NEWS') && $this->continer->get('data.news')->hasAny()) {
                    return true;
                }
                break;
            case 'downloads':
                if ($sec->isGranted('USE_DOWNLOADS') && $this->continer->get('data.downloads')->hasAny()) {
                    return true;
                }
                break;
            case 'feedback':
                if ($sec->isGranted('USE_FEEDBACK') && $this->continer->get('data.feedback')->hasAny()) {
                    return true;
                }
                break;
        }

        return false;
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
     * Counts the number of truthy arguments. Typically used when counting columns.
     *
     * @param mixed...
     * @return int
     */
    public function countTruthy()
    {
        $args = func_get_args();

        $x = 0;
        foreach ($args as $v) {
            if ($v) $x++;
        }

        return $x;
    }

    /**
     * @param string|\DateTime $date
     * @param string $format
     * @param null $timezone
     * @return string
     */
    public function date($date, $format = 'fulltime', $timezone = null)
    {
        $brand = $this->continer->get('brand_stack')->getActive();
        switch ($format) {
            case 'full':
                //D, jS M Y
                $format = $brand->getSetting('core.date_full');
                break;

            case 'fulltime':
                //D, jS M Y g:ia
                $format = $brand->getSetting('core.date_fulltime');
                break;

            case 'day':
                //M j Y
                $format = $brand->getSetting('core.date_day');
                break;

            case 'day_short':
                //M j
                $format = $brand->getSetting('core.date_day_short');
                break;

            case 'time':
                //g:i a
                $format = $brand->getSetting('core.date_time');
                break;
        }

        if ($date instanceof \DateTime) {
            $date = clone $date;
        } else {
            if (ctype_digit((string) $date)) {
                $date = new \DateTime('@'.$date);
            } else {
                try {
                    $date_str = $date;
                    $date     = new \DateTime($date_str);
                } catch (\Exception $e) {}
            }
        }

        if (!($date instanceof \DateTime)) {
            $date_str = (string) $date;
            return "invalid_date($date_str)";
        }

        if ($timezone === null) {
            $person = $this->continer->get('security.token_storage')->getToken()->getUser();
            if ($person && $person instanceof Person) {
                $timezone = $person->getTimezone();
            } else {
                try {
                    $timezone = new \DateTimeZone($brand->getSetting('core.default_timezone'));
                } catch (\Exception $e) {}
            }
        }
        if (is_string($timezone)) {
            try {
                $timezone = new \DateTimeZone($timezone);
            } catch (\Exception $e) {};
        }

        if (!$timezone) {
            $timezone = new \DateTimeZone('UTC');
        }

        $date->setTimezone($timezone);

        return $date->format($format);
    }

    /**
     * @param array  $context
     * @param string $tag_name
     * @param array  $arguments
     *
     * @return string
     */
    public function processPortalPageTag($context, $tag_name, $arguments = array())
    {
        if ($context && isset($context['page']) && $context['page'] instanceof ThemeView) {
            return $context['page']->$tag_name($arguments);
        } else {
            // fallback on page-less tag
            return $this->brand_stack->getActive()->renderTag($tag_name, $arguments);
        }
    }

    /**
     * @param string $tag_name
     * @param array  $arguments
     *
     * @return string
     */
    public function processPortalTag($tag_name, $arguments = array())
    {
        return $this->brand_stack->getActive()->renderTag($tag_name, $arguments);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'portal_support_extension';
    }
}
