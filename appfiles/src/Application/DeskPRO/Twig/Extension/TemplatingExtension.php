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
            'phrase'   => new \Twig_Function_Method($this, 'getPhrase', array('is_safe' => array('html'))),
            'phrase_object'   => new \Twig_Function_Method($this, 'getPhraseObject'),
			'url_fragment' => new \Twig_Function_Method($this, 'urlFragment'),
			'asset_full' => new \Twig_Function_Method($this, 'assetFull'),
			'asset_url' => new \Twig_Function_Method($this, 'assetFull'),
			'url_full' => new \Twig_Function_Method($this, 'urlFull'),
			'url_display' => new \Twig_Function_Method($this, 'urlDisplay'),
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
			'get_data' => new \Twig_Function_Method($this, 'getData'),
			'dp_asset' => new \Twig_Function_Method($this, 'getAssetic'),
			'dp_asset_raw' => new \Twig_Function_Method($this, 'getAsseticRaw'),
			'dp_asset_html' => new \Twig_Function_Method($this, 'htmlGetAssetic', array('is_safe' => array('html'))),
        );
    }

	public function getFilters()
    {
        return array(
			'safe_link_urls' => new \Twig_Filter_Method($this, 'safeLinkUrls', array('is_safe' => array('html'))),
			'safe_link_urls_html' => new \Twig_Filter_Method($this, 'safeLinkUrlsHtml', array('is_safe' => array('html'))),
            'raw_url_encode' => new \Twig_Filter_Method($this, 'rawUrlEncode', array('is_safe' => array('html'))),
			'repeat' => new \Twig_Filter_Method($this, 'strRepeat'),
			'trim' => new \Twig_Filter_Method($this, 'strTrim'),
			'encode_number' => new \Twig_Filter_Method($this, 'encNum', array('is_safe' => array('html'))),
			'decode_number' => new \Twig_Filter_Method($this, 'decNum', array('is_safe' => array('html'))),
			'md5_hash'   => new \Twig_Filter_Method($this, 'getMd5', array('is_safe' => array('html'))),
			'date'   => new \Twig_Filter_Method($this, 'userDate'),
			'slugify' =>  new \Twig_Filter_Method($this, 'slugify'),
			'emphasize_words' => new \Twig_Filter_Method($this, 'emphasizeWords', array('is_safe' => array('html'))),
			'lower' => new \Twig_Filter_Method($this, 'lowercase'),
			'strip_linebreaks' => new \Twig_Filter_Method($this, 'stripLinebreaks'),
			'explode' => new \Twig_Filter_Method($this, 'explodeString'),
			'split' => new \Twig_Filter_Method($this, 'explodeString'),
			'join' => new \Twig_Filter_Method($this, 'implodeArray'),
			'implode' => new \Twig_Filter_Method($this, 'implodeArray'),
			'crc32' => new \Twig_Filter_Method($this, 'crc32'),
			'url_domain' => new \Twig_Filter_Method($this, 'getUrlDomain'),
        );
    }

	public function getUrlDomain($string)
	{
		$urlinfo = @parse_url($string);
		if (!$urlinfo) {
			return $string;
		}

		return $urlinfo['host'];
	}

	public function crc32($string)
	{
		$string = (string)$string;

		return sprintf("%u", crc32($string));
	}

	public function safeLinkUrlsHtml($text)
	{
		$text = preg_replace_callback('#(https?:\/\/[^\s<>]+)#i',function($m) {
			$url = App::getRouter()->generate('agent_redirect_out', array('url' => $m[1]));
			return '<a href="' . $url . '" target="_blank">' . htmlspecialchars($m[1]) . '</a>';
		}, $text);

		return $text;
	}

	public function safeLinkUrls($text)
	{
		$text = htmlspecialchars($text);

		$text = preg_replace_callback('#(https?:\/\/[^\s]+)#i',function($m) {
			$url = App::getRouter()->generate('agent_redirect_out', array('url' => $m[1]));
			return '<a href="' . $url . '" target="_blank">' . htmlspecialchars($m[1]) . '</a>';
		}, $text);

		return $text;
	}

	public function getAssetic($name)
	{
		$assetic_manager = $this->container->getSystemService('assetic_manager');
		return $assetic_manager->getUrl($name);
	}

	public function getAsseticRaw($name)
	{
		$assetic_manager = $this->container->getSystemService('assetic_manager');
		return $assetic_manager->getRawUrls($name);
	}

	public function implodeArray(array $array, $sep = ', ')
	{
		return implode($array, $sep);
	}

	public function explodeString($string, $del = ',') {
		$ret = array();
		$string = (string)$string;

		foreach (explode($del, $string) as $p) {
			$ret[] = trim($p);
		}

		return $ret;
	}

	public function stripLinebreaks($str)
	{
		$str = str_replace(array("\r\n", "\n"), " ", $str);
		$str = str_replace(array("<br />", "<br/>", "<br>"), " ", $str);
		$str = str_replace(array("<p>", "</p>", "<p />", "<p/>"), " ", $str);

		return $str;
	}

	public function htmlGetAssetic($name, $options = array())
	{
		$raw_packs = App::getConfig('debug.raw_assets', array());
		$less_use_css = App::getConfig('debug.less_use_css_dir', false);

		if ($raw_packs && (in_array($name, $raw_packs) OR in_array('all', $raw_packs) OR (in_array('all -vendors', $raw_packs) && $name != 'agent_vendors'))) {
			$urls = $this->getAsseticRaw($name);
		} else {
			$urls = array($this->getAssetic($name));
		}

		$html = array();

		foreach ($urls as $url) {
			$type = Strings::getExtension($url);

			$url .= '?' . DP_BUILD_TIME;

			switch ($type) {
				case 'js':
					$html[] = '<script type="text/javascript" src="' . $url . '"></script>';
					break;
				case 'css':
					if (!isset($options['media'])) {
						$options['media'] = 'screen,print';
					}
					$html[] = '<link rel="stylesheet" type="text/css" media="' . $options['media'] .'" href="' . $url .'" />';
					break;
				case 'less':
					if (!isset($options['media'])) {
						$options['media'] = 'screen,print';
					}

					if ($less_use_css) {
						$url = str_replace('/stylesheets-less/', '/stylesheets/', $url);
						$url = str_replace('.less', '.css', $url);
						$html[] = '<link rel="stylesheet" type="text/css" media="' . $options['media'] .'" href="' . $url .'" />';
					} else {
						$html[] = '<link rel="stylesheet/less" type="text/css" media="' . $options['media'] .'" href="' . $url .'" />';
					}
					break;
			}
		}

		return implode("\n", $html);
	}

	public function getData($id)
	{
		switch ($id) {
			case 'country_names':
				return \Orb\Data\Countries::getCountryNames();
				break;
			default:
				return null;
		}
	}

	public function lowercase($string)
	{
		return strtolower($string);
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
