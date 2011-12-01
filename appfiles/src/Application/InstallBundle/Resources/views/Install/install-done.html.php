<?php $view->extend('InstallBundle::layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Done<?php $view['slots']->stop() ?>
<h3>Install Done</h3>
<p>
	Congratulations, <?php echo $agent['first_name'] ?>! You've successfully installed DeskPRO!
</p>

<h3>Your License</h3>
