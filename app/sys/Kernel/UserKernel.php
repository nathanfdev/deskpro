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
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Kernel;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use Application\DeskPRO\App;

class UserKernel extends AbstractKernel
{
	protected $cache_file = false;

	protected function preResponseHandled(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		$res = parent::preResponseHandled($request, $type, $catch);
		if ($res) {
			return $res;
		}

		$use_cache = false;
		$language_id = null;
		$cache_time = 0;

		if ($request->getMethod() == 'GET' && !isset($_GET['admin_portal_controls']) && !preg_match('#/widget/chat.html#', $request->getPathInfo())) {
			if (!empty($_COOKIE['dp-guest-cache']) || (empty($_COOKIE['dpsid']) && empty($_COOKIE['dpreme']))) {
				if (!isset($_COOKIE['dpsid-agent']) && !isset($_COOKIE['dpsid-admin'])) {
					$use_cache = true;

					if (!empty($_COOKIE['dp-guest-cache'])) {
						$parts = explode('-', $_COOKIE['dp-guest-cache']);
						if (!empty($parts[1])) {
							$language_id = intval($parts[1]);
						}

						$cache_time = intval($parts[0]);
						if ($cache_time && $cache_time < time()) {
							$use_cache = false;
						}
					}
				}
			}
		}

		if ($use_cache) {
			if (!$language_id && isset($_COOKIE['dplid'])) {
				$language_id = intval($_COOKIE['dplid']);
			}

			if (!$language_id) {
				$data = App::getDataService('Language');
				$languages = $data->getAll();
				$default_id = $data->getDefaultId();

				$locales = array('');
				foreach ($languages AS $language) {
					$locales[] = $language->locale;
				}

				try {
					// get the highest priority language if available
					$locale = $request->getPreferredLanguage($locales);
					$accept_languages = $request->getLanguages();
				} catch (\Symfony\Component\DependencyInjection\Exception\InactiveScopeException $e) {
					// the request may not be available, so use the default lang
					$locale = '';
					$accept_languages = array();
				}

				if ($locale) {
					// we have an exact locale match
					foreach ($languages AS $language) {
						if ($language->locale === $locale) {
							$language_id = $language->getId();
							break;
						}
					}
				} else {
					// look for a language match (as there isn't an exact locale match)
					foreach ($accept_languages AS $accept_language) {
						$accept_language = substr($accept_language, 0, 2);
						foreach ($languages AS $language) {
							if (substr($language->locale, 0, 2) == $accept_language) {
								$language_id = $language->getId();
								break 2;
							}
						}
					}
				}

				if (!$language_id) {
					$language_id = $default_id;
				}
			}

			$ttl = App::getSetting('core.page_cache_ttl');
			if ($ttl) {
				$cache_dir = dp_get_tmp_dir() . '/page-cache';
				$uri = $request->getRequestUri();
				$base = substr(preg_replace('#[^a-z0-9_-]#i', '_', $uri), 0, 35);
				$cache_filename = $language_id . '-' . $base . '-' . md5($request->getRequestUri()) . '.cache';
				$cache_file = $cache_dir . '/' . $cache_filename;

				if (file_exists($cache_file)) {
					$use_cache = false;
					if (time() - filemtime($cache_file) <= $ttl) {
						$use_cache = true;
					} else {
						$cache_slam_file = $this->cache_file . '.slam';
						if (file_exists($cache_slam_file) && time() - filemtime($cache_slam_file) < 30) {
							// someone else is going to write it, use the stale data for a bit
							$use_cache = true;
						}
					}

					if ($use_cache) {
						$output = @unserialize(file_get_contents($cache_file));
						if (is_array($output)) {
							if ($output['compressed']) {
								$output['content'] = gzuncompress($output['content']);
							}

							return new Response($output['content'], $output['status'], $output['headers']);
						}
					}
				}

				$this->cache_file = $cache_file;
			}
		}

		return null;
	}

	protected function postResponseHandled($response)
	{
		/** @var $response Response */
		parent::postResponseHandled($response);

		$person = App::getCurrentPerson();
		$logged_in = ($person && $person->getId());
		$skip_cache = true;

		if ($logged_in) {
			if (!empty($_COOKIE['dp-guest-cache'])) {
				\Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie('dp-guest-cache')->send();
			}
		} else {
			if (App::isCacheSkipped()) {
				$cache_time = time() + App::getSetting('core.page_cache_ttl');
			} else {
				$cache_time = !empty($_COOKIE['dp-guest-cache']) ? intval($_COOKIE['dp-guest-cache']) : 0;
				if ($cache_time < time()) {
					$cache_time = 0;
				}
			}

			$skip_cache = ($cache_time > 0);

			$value = $cache_time . '-' . App::getLanguage()->getId();
			if (empty($_COOKIE['dp-guest-cache']) || $value !== $_COOKIE['dp-guest-cache']) {
				\Application\DeskPRO\HttpFoundation\Cookie::makeCookie('dp-guest-cache', $value, 0)->send();
			}
		}

		if (!$logged_in && !$skip_cache && $this->cache_file && $response->headers->get('Content-Type') == 'text/html') {
			$cache_dir = dp_get_tmp_dir() . '/page-cache';
			if (!is_dir($cache_dir)) {
				@mkdir($cache_dir, 0777);
			}

			$cache_slam_file = $this->cache_file . '.slam';
			if (!file_exists($cache_slam_file) || time() - filemtime($cache_slam_file) > 60) {
				$slam_fp = @fopen($cache_slam_file, 'a+');
				if ($slam_fp && flock($slam_fp, LOCK_EX)) {
					// don't take any of the cookies - they'll be things like sessions etc
					$store = array(
						'headers' => $response->headers->all(),
						'status' => $response->getStatusCode(),
						'content' => $response->getContent(),
						'compressed' => false
					);
					if (function_exists('gzcompress')) {
						$store['content'] = gzcompress($store['content']);
						$store['compressed'] = true;
					}

					@file_put_contents($this->cache_file, serialize($store));
					flock($slam_fp, LOCK_UN);
					fclose($slam_fp);
					unlink($cache_slam_file);
				}
			}
		}
	}

	protected function registerAdditionalBundles()
	{
		$bundles = array(
			new \Application\AgentBundle\AgentBundle(), // so templates can work when notiying
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load(DP_ROOT.'/sys/config/user/config_'.$this->getEnvironment().'.php');
	}
}