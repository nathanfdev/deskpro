<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Done<?php $view['slots']->stop() ?>
<h3>Install Done</h3>
<p>
	Congratulations, <?php echo $agent['first_name'] ?>! You've successfully installed DeskPRO!
</p>

<div class="well">
	<a class="btn large primary" href="../admin/">Start using DeskPRO &rarr;</a>
</div>
