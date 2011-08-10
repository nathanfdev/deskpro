<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Templating
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Templating\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\AssetsHelper as BaseAssetsHelper;

use Application\DeskPRO\App;

class AssetsHelper extends BaseAssetsHelper
{
	public function __construct(Request $request, $baseURLs = array(), $version = null, $packages = array())
	{
		$static_path = App::getConfig('static_path');
		if (!$static_path) {
			$static_path = $request->getBasePath() . '/static/';
		}

		$baseURLs[0] = $static_path;

		parent::__construct($request->getBasePath(), $baseURLs, $version, $packages);
	}
}