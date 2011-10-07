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
use Orb\Util\Strings;

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
            'phrase_object'   => new \Twig_Function_Method($this, 'getPhraseObject'),
			'url_fragment' => new \Twig_Function_Method($this, 'urlFragment'),
			'asset_full' => new \Twig_Function_Method($this, 'assetFull'),
			'asset_url' => new \Twig_Function_Method($this, 'assetFull'),
			'url_full' => new \Twig_Function_Method($this, 'urlFull'),
			'url_display' => new \Twig_Function_Method($this, 'urlDisplay'),
			'html_js_pack_raw' => new \Twig_Function_Method($this, 'htmlJsPackRaw', array('is_safe' => array('html'))),
			'html_js_pack' => new \Twig_Function_Method($this, 'htmlJsPack', array('is_safe' => array('html'))),
			'html_css_pack_raw' => new \Twig_Function_Method($this, 'htmlCssPackRaw', array('is_safe' => array('html'))),
			'html_css_pack' => new \Twig_Function_Method($this, 'htmlCssPack', array('is_safe' => array('html'))),
			'deskpro_debug' => new \Twig_Function_Method($this, 'isDebugMode'),
			'render_custom_field' => new \Twig_Function_Method($this, 'renderCustomField', array('is_safe' => array('html'))),
			'render_custom_field_text' => new \Twig_Function_Method($this, 'renderCustomFieldText'),
			'render_custom_field_form' => new \Twig_Function_Method($this, 'renderCustomFieldForm', array('is_safe' => array('html'))),
			'el_uid' => new \Twig_Function_Method($this, 'elUid', array('is_safe' => array('html'))),
			'rand' => new \Twig_Function_Method($this, 'rand', array('is_safe' => array('html'))),
			'is_partial_request' => new \Twig_Function_Method($this, 'isPartialRequest'),
			'str_repeat' => new \Twig_Function_Method($this, 'strRepeat'),
			'is_user_guest' => new \Twig_Function_Method($this, 'isUserGuest'),
			'is_user_loggedin' => new \Twig_Function_Method($this, 'isUserUser'),
			'is_user_agent' => new \Twig_Function_Method($this, 'isUserAgent'),
			'is_user_admin' => new \Twig_Function_Method($this, 'isUserAdmin'),
			'flash_message' => new \Twig_Function_Method($this, 'flashMessage'),
			'compare_type'  => new \Twig_Function_Method($this, 'compareType'),
			'object_path'  => new \Twig_Function_Method($this, 'getObjectPath'),
			'object_path_agent'  => new \Twig_Function_Method($this, 'getObjectPathAgent'),
			'get_type'  => new \Twig_Function_Method($this, 'getType'),
			'debug_var' => new \Twig_Function_Method($this, 'debugVar'),
			'security_token' => new \Twig_Function_Method($this, 'securityToken'),
			'render_usersource' => new \Twig_Function_Method($this, 'renderUsersource', array('is_safe' => array('html'))),
        );
    }

	public function getFilters()
    {
        return array(
            'raw_url_encode' => new \Twig_Filter_Method($this, 'rawUrlEncode', array('is_safe' => array('html'))),
			'repeat' => new \Twig_Filter_Method($this, 'strRepeat'),
			'trim' => new \Twig_Filter_Method($this, 'strTrim'),
			'encode_number' => new \Twig_Filter_Method($this, 'encNum', array('is_safe' => array('html'))),
			'decode_number' => new \Twig_Filter_Method($this, 'decNum', array('is_safe' => array('html'))),
			'md5_hash'   => new \Twig_Filter_Method($this, 'getMd5', array('is_safe' => array('html'))),
			'date'   => new \Twig_Filter_Method($this, 'userDate'),
			'slugify' =>  new \Twig_Filter_Method($this, 'slugify'),
			'emphasize_words' => new \Twig_Filter_Method($this, 'emphasizeWords', array('is_safe' => array('html'))),
        );
    }

	public function emphasizeWords($string, $words)
	{
		if (!is_array($words)) {
			$words = explode(' ', $words);
			array_walk($words, 'trim');
		}

		$string = htmlspecialchars($string);
		foreach ($words as $w) {
			$w = htmlspecialchars($w);
			$string = preg_replace('#(\\b)(' . preg_quote($w, '#') . ')(\\b)#iu', '$1<em>$2</em>$3', $string);
		}

		return $string;
	}

	public function renderUsersource($usersource, $type, array $params = array())
	{
		return $usersource->renderView($this->getTemplating(), $type, $params);
	}

	public function slugify($str)
	{
		return Strings::slugifyTitle($str);
	}

	public function userDate($date, $format = 'F j, Y H:i', $timezone = null)
	{
		if (!$date instanceof \DateTime) {
			if (ctype_digit((string) $date)) {
				$date = new \DateTime('@'.$date);
				$date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			} else {
				$date = new \DateTime($date);
			}
		}

		if ($timezone === null && App::getCurrentPerson()) {
			$timezone = App::getCurrentPerson();
		}

		if ($timezone instanceof \Application\DeskPRO\Entity\Person) {
			$timezone = new \DateTimeZone($timezone->timezone);
		}

		if (null !== $timezone) {
			if (!$timezone instanceof \DateTimeZone) {
				$timezone = new \DateTimeZone($timezone);
			}

			$date->setTimezone($timezone);
		}

		return $date->format($format);
	}

	public function securityToken($name = '', $timeout = 43200)
	{
		return App::getSession()->getEntity()->generateSecurityToken($name, $timeout);
	}

	public function debugVar($var)
	{
		return print_r($var, true);
	}

	public function getType($var, $basename = true)
	{
		// Primitive types
		if (!is_object($var)) {
			$var_type = gettype($var);
		// Classes
		} else {
			$var_type = get_class($var);

			if ($basename) {
				$var_type = Util::getBaseClassname($var_type);
			}

			if ($var instanceof \Doctrine\ORM\Proxy\Proxy) {
				$var_type = preg_replace('#(^|\\\\)ApplicationDeskPROEntity(.*?)Proxy$#', '$2', $var_type);
			}
		}

		return $var_type;
	}

	public function getObjectPath($object, array $params = array(), $context = 'user')
	{
		$generator = $this->container->get('router')->getGenerator();
		return $generator->generateObjectUrl($object, $params, $context);
	}

	public function getObjectPathAgent($object, array $params = array())
	{
		return $this->getObjectPath($object, $params, 'agent');
	}

	public function compareType($var, $type)
	{
		// Primitive types
		if (!is_object($var)) {
			$var_type = gettype($var);
			return strpos($var_type, $type) !== false;

		// Classes
		} else {
			$var_type = get_class($var);

			// Passes Some\MyClass as well as just MyClass, but not SomeOther\MyClass against Some\MyClass
			return (strpos($var_type, $type) !== false AND Util::getBaseClassname($var_type) == Util::getBaseClassname($type));
		}
	}

	public function flashMessage($name)
	{
		$session = $this->container->get('session');
		return $session->getFlash($name, null);
	}

	public function encNum($num)
	{
		return Util::baseEncode((int)$num, Util::LETTERS_ALPHABET);
	}

	public function decNum($num)
	{
		return Util::baseDecode((int)$num, Util::LETTERS_ALPHABET);
	}

	public function rand($min = 1, $max = 10)
	{
		return mt_rand((int)$min, (int)$max);
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

	public function getPhraseObject($phrase_name, $property = null)
	{
		return $this->container->get('deskpro.core.translate')->getPhraseObject($phrase_name, $property);
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
				$html .= '<script>console.error("[JS] Tried loading invalid pack: %s", "'.$name.'");</script>';
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
				$html .= '<script>console.error("[JS] Tried loading invalid pack: %s", "'.$name.'");</script>';
			}

			return $html;
		}

		$file =	$this->container->get('templating.helper.assets')->getUrl('build/' . $pack['out']);
		$html = '<script src="'.$file.'"></script>';

		return $html;
	}

	public function htmlCssPackRaw($name)
	{
		$pack = App::getConfig($name, null, 'css-sources');
		if (!$pack) {
			$html = '<!-- UNKNOWN CSS PACK: ' . $name . ' -->';
			if (App::isDebug()) {
				$html .= '<script>console.error("[CSS] Tried loading invalid pack: %s", "'.$name.'");</script>';
			}

			return $html;
		}

		$use_less = App::getConfig('debug.use_less_css', false) && isset($pack['less_files']);

		$html = array();
		foreach ($pack['files'] as $k => $file) {

			if ($use_less && isset($pack['less_files'][$k])) {
				$url = $this->container->get('templating.helper.assets')->getUrl($pack['less_files'][$k]);
				$html[] = '<link rel="stylesheet/less" type="text/css" media="'.$pack['media'].'" href="'.$url.'" />';
			} else {
				$url = $this->container->get('templating.helper.assets')->getUrl($file);
				$html[] = '<link rel="stylesheet" type="text/css" media="'.$pack['media'].'" href="'.$url.'" />';
			}
		}

		return implode("\n", $html);
	}

	public function htmlCssPack($name, $force_raw = false)
	{
		$raw_packs = App::getConfig('debug.raw_css_packs', array());

		if ($force_raw OR in_array($name, $raw_packs) OR ($name != 'agent.vendors' AND in_array('all', $raw_packs))) {
			return $this->htmlCssPackRaw($name);
		}

		$pack = App::getConfig($name, null, 'css-sources');
		if (!$pack) {
			$html = '<!-- UNKNOWN CSS PACK: ' . $name . ' -->';
			if (App::isDebug()) {
				$html .= '<script>console.error("[CSS] Tried loading invalid pack: %s", "'.$name.'");</script>';
			}

			return $html;
		}

		$file =	$this->container->get('templating.helper.assets')->getUrl('build-css/' . $pack['out']);
		$html = '<link rel="stylesheet" type="text/css" media="' . $pack['media'] . '" href="'.$file.'" />';

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
