<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111128080717 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Update filters for open/pending awaiting agent/user change');

		$filters = App::getDb()->fetchAll("SELECT * FROM ticket_filters");

		try {

			foreach ($filters as $f) {
				$terms = unserialize($f['terms']);
				$changed = false;
				for ($i = 0; $i < count($terms); $i++) {
					if ($terms[$i]['type'] == 'status') {
						if ($terms[$i]['options']['status'] == 'open') {
							$changed = true;
							$terms[$i]['options']['status'] = 'awaiting_agent';
						} elseif ($terms[$i]['options']['status'] == 'pending') {
							$changed = true;
							$terms[$i]['options']['status'] = 'awaiting_user';
						}
					}
				}

				if ($changed) {
					App::getDb()->update('ticket_filters', array('terms' => serialize($terms)), array('id' => $f['id']));
				}
			}

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
