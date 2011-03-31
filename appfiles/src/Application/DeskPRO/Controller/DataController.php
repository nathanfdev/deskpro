<?php

namespace Application\DeskPRO\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Util;
use \Orb\Util\Arrays;

class DataController extends AbstractController
{
	public function interfaceDataAction()
	{
		$what = $this->in->getCleanValueArray('types', 'string', 'discard');
		
		$js = array();

		if (in_array('tickets', $what)) {
			$js[] = 'DeskPRO_Window.set(\'ticketDepToCatMap\', ' . json_encode(App::getEntityRepository('DeskPRO:TicketCategory')->departmentToCategoryMap()) . ');';

			// Ticket display elements
			$part = array();
			$part[] = "(function() { var tmp = {};\n";
			$display_elements = App::getOrm()->createQuery("
				SELECT d
				FROM DeskPRO:DepartmentTicketDisplay d
				ORDER BY d.display_order ASC
			")->execute();
			$done_deps = array();
			foreach ($display_elements as $d) {
				if (!in_array($d['department_id'], $done_deps)) {
					$done_deps[] = $d['department_id'];
					$part[] = "tmp[{$d['department_id']}] = [];";
				}

				$token = '%%%replacetoken' . mt_rand(1000,9999) . '%%%';

				$line = "tmp[{$d['department_id']}].push(" . json_encode(array(
					'element_type' => $d['element_type'],
					'element_id' => $d['element_id'],
					'initial_state' => $d['initial_state'],
					'check' => $token
				)) . ");";

				// Cheap and simple way to insert a function literal while still using json_encode for the other values
				$line = str_replace('"'.$token.'"', $d->compileToJavascript(), $line);
				$part[] = $line;
			}
			$part[] = "\n DeskPRO_Window.set('ticketDisplay', tmp);\n })();";

			$js[] = implode(" ", $part);
		}

		
		$js = implode("\n", $js);
		$response = App::getResponse();
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}
}