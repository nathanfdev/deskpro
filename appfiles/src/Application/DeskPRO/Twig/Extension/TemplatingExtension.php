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

use Orb\Util\Util;
	
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
			'url_fragment' => new \Twig_Function_Method($this, 'urlFragment'),
            'md5_hash'   => new \Twig_Function_Method($this, 'getMd5'),
			'asset_full' => new \Twig_Function_Method($this, 'assetFull'),
			'asset_url' => new \Twig_Function_Method($this, 'assetFull'),
			'url_full' => new \Twig_Function_Method($this, 'urlFull'),
			'url_display' => new \Twig_Function_Method($this, 'urlDisplay'),
			'html_js_pack_raw' => new \Twig_Function_Method($this, 'htmlJsPackRaw', array('is_safe' => array('html'))),
			'html_js_pack' => new \Twig_Function_Method($this, 'htmlJsPack', array('is_safe' => array('html'))),
			'deskpro_debug' => new \Twig_Function_Method($this, 'isDebugMode'),
			'render_custom_field' => new \Twig_Function_Method($this, 'renderCustomField', array('is_safe' => array('html'))),
			'render_custom_field_text' => new \Twig_Function_Method($this, 'renderCustomFieldText'),
			'render_custom_field_form' => new \Twig_Function_Method($this, 'renderCustomFieldForm', array('is_safe' => array('html'))),
			'el_uid' => new \Twig_Function_Method($this, 'elUid', array('is_safe' => array('html'))),
			'is_partial_request' => new \Twig_Function_Method($this, 'isPartialRequest'),
			'str_repeat' => new \Twig_Function_Method($this, 'strRepeat'),
			'is_user_guest' => new \Twig_Function_Method($this, 'isUserGuest'),
			'is_user_loggedin' => new \Twig_Function_Method($this, 'isUserUser'),
			'is_user_agent' => new \Twig_Function_Method($this, 'isUserAgent'),
			'is_user_admin' => new \Twig_Function_Method($this, 'isUserAdmin'),
        );
    }

	public function getFilters()
    {
        return array(
            'raw_url_encode' => new \Twig_Filter_Method($this, 'rawUrlEncode', array('is_safe' => array('html'))),
			'repeat' => new \Twig_Filter_Method($this, 'strRepeat'),
			'trim' => new \Twig_Filter_Method($this, 'strTrim'),
        );
    }

	public function isUserGuest($person = null)
	{
		if (!$person) {
			$person = $this->container->get('deskpro.session_person');
		}

		if (!$person['id']) {
			return true;
		}

		return false;
	}

	public function isUserUser($person = null)
	{
		if (!$person) {
			$person = $this->container->get('deskpro.session_person');
		}

		if ($person['id']) {
			return true;
		}

		return false;
	}

	public function isUserAgent($person)
	{
		if (!$person) {
			$person = $this->container->get('deskpro.session_person');
		}

		if ($person['is_agent']) {
			return true;
		}

		return false;
	}

	public function isUserAdmin($person)
	{
		if (!$person) {
			$person = $this->container->get('deskpro.session_person');
		}

		if ($person['is_admin']) {
			return true;
		}

		return false;
	}

	/**
	 * Checks the special _partial flag in incoming requests to see if the user wants a partial
	 * 
	 * @return bool
	 */
	public function isPartialRequest()
	{
		return $this->container->get('request')->isPartialRequest();
	}

	public function strRepeat($str, $count = 1)
	{
		return str_repeat($str, $count);
	}

	public function strTrim($str)
	{
		return trim($str);
	}

	/**
	 * A unique ID generator usually used to generate unique element ID's. Unique
	 * ID's are generally needed only in the agent interface where things share the same dom.
	 *
	 * @param string $prefix
	 * @return string
	 */
	public function elUid($prefix = 'dp_')
	{
		return $prefix
			   . Util::baseEncode(time() - strtotime('-15 days'), 'base36') // 4 digits. 15 days to save a few digits
			   . Util::baseEncode(mt_rand(36, 1295), 'base36') // 2 digits
			   . Util::baseEncode(Util::requestUniqueId(), 'base36'); // 1-2 digits
	}

	/**
	 * Just gets a full helpdesk URL minus the http:// and www bits.
	 * Makes it prettier when displaying links in emails.
	 * 
	 * @param $name
	 * @param array $parameters
	 * @return mixed|string
	 */
	public function urlDisplay($name, array $parameters = array())
	{
		$url = $this->urlFull($name, $parameters);
		$url = preg_replace('#^https?://(www\.)?#i', '', $url);

		return $url;
	}

	public function urlFull($name, array $parameters = array())
	{
		// The last param of generate when true gives a full URL.
		// But this is based off of 1) The current URL and 2) doesnt work in console
		// So we use this for when we need to generate a helpdesk URL based on the setting

		$url = $this->container->get('router')->getGenerator()->generate($name, $parameters, false);
		return App::getSetting('core.deskpro_url') . ltrim($url, '/');
	}

	public function urlFragment($name, array $parameters = array())
	{
		return $this->container->get('router')->getGenerator()->generateFragment($name, $parameters, false);
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

		if ($force_raw OR in_array($name, $raw_packs) OR ($name != 'agent.vendors' AND in_array('all', $raw_packs))) {
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
