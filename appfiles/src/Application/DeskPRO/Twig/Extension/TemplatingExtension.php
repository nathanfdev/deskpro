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
			'asset_url' => new \Twig_Function_Method($this, 'assetFull'),
			'html_js_pack_raw' => new \Twig_Function_Method($this, 'htmlJsPackRaw', array('is_safe' => array('html'))),
			'html_js_pack' => new \Twig_Function_Method($this, 'htmlJsPack', array('is_safe' => array('html'))),
			'deskpro_debug' => new \Twig_Function_Method($this, 'isDebugMode'),
			'render_custom_field' => new \Twig_Function_Method($this, 'renderCustomField', array('is_safe' => array('html'))),
			'render_custom_field_text' => new \Twig_Function_Method($this, 'renderCustomFieldText'),
			'render_custom_field_form' => new \Twig_Function_Method($this, 'renderCustomFieldForm', array('is_safe' => array('html'))),
        );
    }

	public function getFilters()
    {
        return array(
            'raw_url_encode' => new \Twig_Filter_Method($this, 'rawUrlEncode', array('is_safe' => array('html'))),
        );
    }

	public function renderCustomField($display_array, array $vars = array())
	{
		$handler = $display_array['handler'];
		$vars = array_merge($display_array, $vars);
		return $handler->renderHtml($display_array['value'], $vars);
	}

	public function renderCustomFieldForm($display_array, array $vars = array())
	{
		$handler = $display_array['handler'];
		$formView = $display_array['formView'];

		$vars = array_merge($display_array, $vars);
		
		return $handler->renderFormHtml($formView, $vars);
	}

	public function renderCustomFieldText($display_array, array $vars = array())
	{
		$handler = $display_array['handler'];
		$vars = array_merge($display_array, $vars);
		return $handler->renderText($display_array['value'], $vars);
	}

	public function getPhrase($phrase_name, array $vars = array())
	{
		return $this->container->get('deskpro.core.translate')->phrase($phrase_name, $vars);
	}

	public function isDebugMode()
	{
		return App::isDebug();
	}

	public function getMd5($string)
	{
		return md5($string);
	}

	public function assetFull($location)
	{
		return App::getSetting('core.deskpro_assets_full_url') . ltrim($location, '/');
	}

	public function htmlJsPackRaw($name)
	{
		$pack = App::getConfig($name, null, 'js-sources');
		if (!$pack) {
			$html = '<!-- UNKNOWN JS PACK: ' . $name . ' -->';
			if (App::isDebug()) {
				$html .= '<script>console.error("Tried loading invalid pack: %s", "'.$name.'");</script>';
			}

			return $html;
		}

		$html = array();
		foreach ($pack['files'] as $file) {
			$url = $this->container->get('templating.helper.assets')->getUrl($file);
			$html[] = '<script src="' . $url . '"></script>';
		}

		return implode("\n", $html);
	}

	public function htmlJsPack($name, $force_raw = false)
	{
		$raw_packs = App::getConfig('debug.raw_js_packs', array());

		if ($force_raw OR in_array($name, $raw_packs)) {
			return $this->htmlJsPackRaw($name);
		}

		$pack = App::getConfig($name, null, 'js-sources');
		if (!$pack) {
			$html = '<!-- UNKNOWN JS PACK: ' . $name . ' -->';
			if (App::isDebug()) {
				$html .= '<script>console.error("Tried loading invalid pack: %s", "'.$name.'");</script>';
			}

			return $html;
		}

		$file =	$this->container->get('templating.helper.assets')->getUrl('build/' . $pack['out']);
		$html = '<script src="'.$file.'"></script>';
		
		return $html;
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
