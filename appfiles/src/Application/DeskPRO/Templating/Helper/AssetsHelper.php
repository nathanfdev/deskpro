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

class AssetsHelper extends BaseAssetsHelper
{
	public function __construct(Request $request, $baseURLs = array(), $version = null, $packages = array())
	{
		foreach ($baseURLs as &$u) {
			$u = str_replace('BASE_PATH', $request->getBasePath(), $u);
		}

		parent::__construct($request->getBasePath(), $baseURLs, $version, $packages);
	}
}