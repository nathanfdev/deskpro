<?php

namespace Application\SysBundle\Controller;

use Application\DeskPRO\App;

class ResourceController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
	public function userCssAction($filename)
	{
		$style = $this->container->getSystemService('style');
		if (!$style) {
			$style = new \Application\DeskPRO\Entity\Style();
		}

		$cache_name = md5($style->id . ':' . $filename);

		$cache = App::getCache('portal');
		$info = false;
		$nocache = false;
		if (!App::getConfig('debug.no_css_cache')) {
			$nocache = true;
			$info = $cache->load($cache_name);
		}
		if (!$info || $info['css_updated'] < $style->css_updated->getTimestamp()) {
			$file = file_get_contents(DP_WEB_ROOT.'/deskpro_assets/' . $style->css_dir . '/' . $filename);
			$userstyle = new \Application\DeskPRO\Style\UserStyle($file);

			$file = $userstyle->compileCss($style->getCssVars());

			// Fix url to static
			$file = str_replace('url(../../', 'url(../../../../static/', $file);

			// Strip comments
			$file = preg_replace('#/\*[^*]*.*?\*/#s', '', $file);

			// Superflous whitespace
			$file = preg_replace("#\n{2,}#", "\n", $file);
			$file = preg_replace("#\s*\{\s*#", "{", $file);
			$file = preg_replace("#\s*\;\s*#", ";", $file);
			$file = preg_replace("#\s*\:\s*#", ":", $file);

			$info['css_updated'] = $style->css_updated->getTimestamp();
			$info['file'] = $file;

			if (!$nocache) {
				$cache->save($info, $cache_name);
			}
		}

		$file = $info['file'];

		$response = App::getResponse();
		$response->headers->set('Content-Type', 'text/css; filename=' . $filename);
		$response->headers->set('Content-Length', strlen($file));
		if ($nocache) {
			$response->setTtl(31556926);
			$response->setExpires(new \DateTime('+1 year'));
			$response->getLastModified(new \DateTime('-1 day'));
			$response->setMaxAge(31556926);
		}
		$response->setContent($file);

		return $response;
	}
}
