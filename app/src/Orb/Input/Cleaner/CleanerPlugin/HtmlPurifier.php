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
			'html_email_basicclean',
			'html_email_preclean',
			'html_fix',
		);
	}

	public function cleanValue($value, $type, array $options, Cleaner $cleaner)
	{
		$value = $cleaner->getCleaner('basic')->cleanValue($value, 'string', array(), $cleaner);

		if (!$value || strpos($value, '<') === false) {
			return $value;
		}

		#------------------------------
		# Basic email clean
		#------------------------------

		if ($type == 'html_email_preclean') {
			$value = str_replace(array('<o:p>', '</o:p>'), array('', ''), $value);
			$value = Strings::extractBodyTag($value);
			$value = Strings::decodeWhitespaceHtmlEntities($value);
			return $value;
		}

		if ($type == 'html_email_basicclean') {
			return $value;
		}

		#------------------------------
		# HTML Purifier cleaners
		#------------------------------

		require_once DP_ROOT.'/vendor/htmlpurifier/HTMLPurifier.standalone.php';

		$purifier = new \HTMLPurifier();
		$config = $this->getConfigForType($type);

		if ($type == 'html_email') {
			// Cut to the body, also cuts out multiple xml decls
			// Even if the client didnt send it, DOMDocument from cutter etc wraps body wanyway
			$value = Strings::extractBodyTag($value);
		}

		$value = $purifier->purify($value, $config);
		$value = preg_replace('#class="([a-zA-Z0-9]*)MsoNormal([a-zA-Z0-9]*)"#i', 'class="$1MsoNormal$2" style="margin:0;"', $value);
		$value = preg_replace_callback('#<[^>]*>#', function ($m) {
			return preg_replace('#(.*?)style="(.*?)"(.*?)style="(.*?)"#', '$1style="$2;$3"$4', $m[0]);
		}, $value);

		if ($type == 'html_email') {
			$value = $this->cleanValue($value, 'html_email_basicclean', $options, $cleaner);
			$value = Strings::decodeWhitespaceHtmlEntities($value);
			$value = Strings::trimHtml($value);
			$value = Strings::trimHtmlAdvanced($value);
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
		$config->set('Core.Encoding', 'UTF-8');

		switch ($type) {
			case 'html':
				$config->set('HTML.Allowed', 'br,div,em,strong,span,h1,h2,h3,h4,h5,h6,table,thead,tbody,tfoot,tr,td,th,a[href],ul,li,dd,dt,dl,ol,p,pre,code,blockquote');
				$config->set('HTML.AllowedAttributes', '*.style,*.class,img.src');
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('HTML.TidyLevel', 'medium');
				$config->set('AutoFormat.RemoveEmpty', false);
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
				$config->set('HTML.AllowedAttributes', 'a.href,*.style,*.class');
				$config->set('Attr.AllowedClasses', 'MsoNormal');
				$config->set('AutoFormat.Linkify', true);
				$config->set('URI.DisableExternalResources', true);
				$config->set('AutoFormat.RemoveEmpty', false);
				$config->set('CSS.AllowedProperties', array('font-weight', 'font-style'));
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('HTML.TidyLevel', 'medium');
				break;

			case 'html_fix':
				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
				$config->set('HTML.TidyLevel', 'none');
				break;
		}

		return $config;
	}
}
