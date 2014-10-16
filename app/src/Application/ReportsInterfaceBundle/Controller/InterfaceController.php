<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ReportsInterfaceBundle\Controller;

use Application\DeskPRO\Translate\JsExporter;
use Symfony\Component\HttpFoundation\Response;

class InterfaceController extends AbstractController
{
	####################################################################################################################
	# load-view
	####################################################################################################################

	public function loadViewAction($view_name)
	{
		$load_data = null;

		// Load data from a route at the same time
		if ($this->in->getString('load_data')) {
			try {
				$route_info = $this->container->getRouter()->match($this->in->getString('load_data'));
			} catch (\Exception $e) {
				$route_info = null;
			}

			if ($route_info) {
				$ctrl_path = $route_info['_controller'];
				unset($route_info['_controller']);
				unset($route_info['_route']);
				$path_vars = $route_info;

				if ($ctrl_path) {
					$load_data = $this->forward($ctrl_path, $path_vars)->getContent();
				}
			}
		}

		$view_name = preg_replace('#[^a-zA-Z0-9_\-/\.]#', '', $view_name);
		$view_name = str_replace('/', ':', $view_name);
		$view_name = str_replace('.html', '.html.twig', $view_name);

		$rendered = null;
		if ($this->tpl->exists("ReportsInterfaceBundle:$view_name")) {
			$rendered = $this->renderView("ReportsInterfaceBundle:$view_name");
		}

		if ($load_data) {
			$rendered = "<script type=\"application/json\" class=\"DP_LOAD_DATA\">" . $load_data . "</script>$rendered";
		}

		return $this->createResponse($rendered);
	}


	####################################################################################################################
	# multi-load-view
	####################################################################################################################

	public function multiLoadViewAction()
	{
		$views = array();

		foreach ($this->in->getCleanValueArray('views', 'string', 'discard') as $view_name) {
			$id = $view_name;
			$view_name = preg_replace('#[^a-zA-Z0-9_\-/\.]#', '', $view_name);
			$view_name = str_replace('/', ':', $view_name);
			$view_name = str_replace('.html', '.html.twig', $view_name);

			$rendered = null;
			if ($this->tpl->exists("ReportsInterfaceBundle:$view_name")) {
				$rendered = $this->renderView("ReportsInterfaceBundle:$view_name");
			}

			$views[] = array(
				'id'       => $id,
				'template' => "ReportsInterfaceBundle:$view_name",
				'source'   => $rendered
			);
		}

		return $this->createJsonResponse($views);
	}


	####################################################################################################################
	# load-lang
	####################################################################################################################

	public function loadLangAction($_format)
	{
		$js_exporter = new JsExporter($this->container->getTranslator());

		$get_phrases = include(DP_ROOT.'/languages/expose-js.php');
		$get_phrases = $get_phrases['reports'];

		if ($_format == 'js') {
			$varname = 'DP_LANG';
			if ($this->in->getString('varname')) {
				$varname = $this->in->getString('varname');
			}

			$res = new Response(
				$js_exporter->exportToJsFile($varname, $get_phrases),
				200,
				array('Content-Type' => 'text/javascript')
			);
		} else {
			$res = new Response(
				$js_exporter->exportToJson($get_phrases),
				200,
				array('Content-Type' => 'application/json')
			);
		}

		return $res;
	}
}