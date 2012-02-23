<?php
/**
 * Orb
 *
 * @package Orb
 * @category Input
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Input\Cleaner\CleanerPlugin;

use Orb\Input\Cleaner\Cleaner;
use Orb\Util\Strings;

/**
 * Uses HTMLPurifier to clean HTML input
 */
class HtmlPurifier implements CleanerPlugin
{
	public function getCleanerId()
	{
		return 'html_purifier';
	}

	public function getCleanerTypes()
	{
		return array(
			'html',
			'simple_html',
			'html_email',
		);
	}

	public function cleanValue($value, $type, array $options, Cleaner $cleaner)
	{
		$value = $cleaner->getCleaner('basic')->cleanValue($value, 'string', array(), $cleaner);

		if (!$value || strpos($value, '<') === false) {
			return $value;
		}

		require_once DP_ROOT.'/vendor/htmlpurifier/HTMLPurifier.standalone.php';

		$purifier = new \HTMLPurifier();
		$config = $this->getConfigForType($type);

		$value = $purifier->purify($value, $config);

		return $value;
	}

	/**
	 * @param string $type
	 * @return \HTMLPurifier_Config
	 */
	public function getConfigForType($type)
	{
		$config = \HTMLPurifier_Config::createDefault();
		$config->set('Cache.DefinitionImpl', null);

		switch ($type) {
			case 'html':
				$config->set('HTML.Allowed', 'div,em,strong,span,h1,h2,h3,h4,h5,h6,table,thead,tbody,tfoot,tr,td,th,a[href],ul,li,dd,dt,dl,ol,p,pre,code');
				$config->set('URI.DisableExternalResources', true);
				break;

			case 'html_simple':
				$config->set('HTML.Allowed', 'em,strong,a[href],ul,li,dd,dt,dl,ol,p,span');
				$config->set('AutoFormat.AutoParagraph', true);
				$config->set('AutoFormat.Linkify', true);
				$config->set('URI.DisableExternalResources', true);
				$config->set('AutoFormat.RemoveEmpty', true);
				$config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
				$config->set('AutoFormat.RemoveEmpty', true);
				break;

			case 'html_email':
				$config->set('HTML.Allowed', 'em,strong,a[href],ul,li,dd,dt,dl,ol,p,span');
				$config->set('AutoFormat.Linkify', true);
				$config->set('URI.DisableExternalResources', true);
				$config->set('AutoFormat.RemoveEmpty', true);
				$config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
				$config->set('AutoFormat.RemoveEmpty', true);
				$config->set('CSS.AllowedFonts', array('courier', 'courier new', 'monospace', 'monospaced', 'monaco'));
				$config->set('CSS.AllowedProperties', array('font-family', 'font-weight', 'font-style'));
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('HTML.TidyLevel', 'medium');
				break;
		}

		return $config;
	}
}
