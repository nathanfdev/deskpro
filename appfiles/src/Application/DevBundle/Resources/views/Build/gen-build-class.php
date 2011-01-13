<?php $view->extend('DevBundle::layout.php') ?>
<h1>New Build Class</h1>
<ul>
	<li>Copy this code into <var>appfiles/src/Application/DeskPRO/Build/<strong><?php echo $build_classname ?>.php</strong></var></li>
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
		$this->output->write('My Upgrade Code');
		return Upgrader::STEP_DONE;
	}
}
</textarea>

<p style="font-size: 8pt">
	Note: You should pull latest changes from version control before creating a build class in case
	someone else has committed a newer build than you.
</p>