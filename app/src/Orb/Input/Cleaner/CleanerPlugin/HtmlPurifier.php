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
 * Orb
 *
 * @package Orb
 * @category Input
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
		$value = Strings::trimHtml($value);

		if ($type == 'html_email') {
			$value = str_replace(array('<o:p>', '</o:p>'), array('<p>', '</p>'), $value);
			$value = preg_replace('#<span>(\s|&nbsp;)*</span>#u', '', $value);
			$value = preg_replace('#\s*<p>(\s|&nbsp;)*</p>\s*#u', '', $value);
			$value = str_replace('<p>', '', $value);
			$value = str_replace('</p>', '<__dp_old_p__>', $value);
			$value = str_replace('<__dp_old_p__><__dp_old_p__>', '<br /><br />', $value);
			$value = str_replace('<__dp_old_p__>', '<br /><br />', $value);
		}

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
				$config->set('HTML.Allowed', 'br,div,em,strong,span,h1,h2,h3,h4,h5,h6,table,thead,tbody,tfoot,tr,td,th,a[href],ul,li,dd,dt,dl,ol,p,pre,code,blockquote');
				$config->set('URI.DisableExternalResources', true);
				break;

			case 'html_simple':
				$config->set('HTML.Allowed', 'em,strong,a[href],ul,li,dd,dt,dl,ol,p,span,br');
				$config->set('AutoFormat.AutoParagraph', true);
				$config->set('AutoFormat.Linkify', true);
				$config->set('URI.DisableExternalResources', true);
				$config->set('AutoFormat.RemoveEmpty', true);
				$config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
				$config->set('AutoFormat.RemoveEmpty', true);
				break;

			case 'html_email':
				$config->set('HTML.AllowedElements', 'em,strong,a,ul,li,dd,dt,dl,ol,p,span,br,hr,table,thead,tbody,tfoot,tr,td,th,pre,code,div,blockquote');
				$config->set('HTML.AllowedAttributes', 'a.href,*.style');
				$config->set('AutoFormat.Linkify', true);
				$config->set('URI.DisableExternalResources', true);
				$config->set('AutoFormat.RemoveEmpty', false);
				$config->set('CSS.AllowedFonts', array(
					'Arial',
					'Comic Sans MS',
					'Courier',
					'Courier New',
					'Geneva',
					'Georgia',
					'Helvetica',
					'Helvetica Neue',
					'Impact',
					'Lucida Grande',
					'Marker Felt',
					'Microsoft Sans Serif',
					'<onaco',
					'monospace',
					'monospaced',
					'Palatino',
					'Papyrus',
					'sans-serif',
					'serif',
					'Tahoma',
					'Times',
					'Times New Roman',
					'Trebuchet MS',
					'Verdana',
					'Wingdings',
				));
				$config->set('CSS.AllowedProperties', array('font-family', 'font-weight', 'font-style', 'font-size', 'color'));
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('HTML.TidyLevel', 'medium');
				break;
		}

		return $config;
	}
}
