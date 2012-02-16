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

namespace Application\DeskPRO\Templating\Asset;

use Symfony\Component\Templating\Asset\UrlPackage as BaseUrlPackage;

use Application\DeskPRO\App;

class UrlPackage extends BaseUrlPackage
{
	public function __construct($baseUrls = array(), $version = null, $format = null)
    {
		$real = array();
		foreach ((array)$baseUrls as $burl) {
			if (!$burl OR $burl == 'CONFIG_HTTP' OR $burl == 'CONFIG_SSL') {
				$type = $burl;
				$burl = false;
				if (!$type) {
					$type = 'CONFIG_HTTP';
				}

				if ($type == 'CONFIG_SSL') {
					$burl = App::getConfig('static_ssl_path');
				}

				if (!$burl) {
					$burl = App::getConfig('static_path');
				}
			}

			if (!$burl AND App::has('request')) {
				$request = App::get('request');
				$burl = $request->getBasePath() . '/web';
			}

			if ($burl) {
				$real[] = $burl;
			}
		}

        parent::__construct($real, $version, $format);
    }
}
