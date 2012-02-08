<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Templating
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Application\DeskPRO\App;

use Orb\Util\Util;

class UserTemplatingExtension extends \Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getFunctions()
    {
        return array(
            'portal_js'   => new \Twig_Function_Method($this, 'portalJs', array('is_safe' => array('html'))),
			'portal_css' => new \Twig_Function_Method($this, 'portalCss', array('is_safe' => array('html'))),
            'portal_section'   => new \Twig_Function_Method($this, 'portalSection', array('is_safe' => array('html'))),
        );
    }

	public function portalJs($section)
	{
		$html = array();

		$portal_page = $this->container->get('deskpro.user_portal_page');
		foreach ($portal_page->getJsAssets($section) as $asset) {
			$url = $this->container->get('templating.helper.assets')->getUrl($asset);
			$html[] = '<script src="' . $url . '"></script>';
		}

		return implode("\n", $html);
	}

	public function portalCss($section)
	{
		$html = array();

		$portal_page = $this->container->get('deskpro.user_portal_page');
		foreach ($portal_page->getCssAssets($section) as $asset) {
			$url = $this->container->get('templating.helper.assets')->getUrl($asset);
			$html[] = '<link rel="stylesheet" type="text/css" href="'.$url.'" />';
		}

		return implode("\n", $html);
	}

	public function portalSection($section)
	{
		$portal_page = $this->container->get('deskpro.user_portal_page');
		return $portal_page->getSectionHtml($section);
	}

	public function getFilters()
    {
        return array();
    }

	 public function getName()
    {
        return 'deskpro_user_templating';
    }
}
