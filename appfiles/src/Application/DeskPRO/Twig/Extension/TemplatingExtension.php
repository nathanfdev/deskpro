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

namespace Application\DeskPRO\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Application\DeskPRO\App;
	
class TemplatingExtension extends \Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getTemplating()
    {
        return $this->container->get('templating');
    }

    public function getFunctions()
    {
        return array(
            'phrase'   => new \Twig_Function_Method($this, 'getPhrase'),
            'md5_hash'   => new \Twig_Function_Method($this, 'getMd5'),
			'asset_full' => new \Twig_Function_Method($this, 'assetFull'),
        );
    }

	public function getFilters()
    {
        return array(
            'raw_url_encode' => new \Twig_Filter_Method($this, 'rawUrlEncode', array('is_safe' => array('html'))),
        );
    }

	public function getPhrase($phrase_name, array $vars = array())
	{
		return $this->container->get('deskpro.core.translate')->phrase($phrase_name, $vars);
	}

	public function getMd5($string)
	{
		return md5($string);
	}

	public function assetFull($location, $packageName = null)
	{
		$url = $this->container->get('templating.helper.assets')->getUrl($location, $packageName);
		return App::getSetting('core.deskpro_url') . ltrim($url, '/');
	}

	public function rawUrlEncode($str)
	{
		return rawurlencode($str);
	}

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'deskpro_templating';
    }
}
