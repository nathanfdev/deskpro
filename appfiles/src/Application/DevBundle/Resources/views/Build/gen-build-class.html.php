<?php $view->extend('DevBundle::layout.html.php') ?>
<h1>New Build Class</h1>
<ul>
	<li>Copy this code template into <var>appfiles/src/Application/DeskPRO/Build/Upgrade/<strong><?php echo $build_classname ?>.php</strong></var> and edit it</li>
	<li>Update <var>appfiles/sys/<strong>VERSION</strong></var> and set it to <var><?php echo $build_string ?></var></li>
</ul>

<textarea style="width: 95%; height: 280px; font-family: 'Monaco', 'Courier New', monospace;">
&lt?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class <?php echo $build_classname ?> extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("SOME SQL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
</textarea>

<p style="font-size: 8pt">
	Note: You should pull latest changes from version control before creating a build class in case
	someone else has committed a newer build than you.
</p>