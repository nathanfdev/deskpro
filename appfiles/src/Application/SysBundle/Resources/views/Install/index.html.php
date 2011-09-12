<?php $view->extend('SysBundle::layout.html.php') ?>

<h1>1. Edit Config</h1>
<p>
	Before we fill the database with install data, you must copy
	<var>/appfiles/config.new.php</var> to <var>config.php</var> and
	edit the database values.
</p>

<?php if ($config_contents): ?>
	<p>I've detected that you've already filled in config.php. Here it is:</p>
	<textarea style="width: 95%; height: 200px; font-family: 'Monaco', 'Courier New', monospace;"><?php echo $view->escape($config_contents) ?></textarea>
	<p>
		<a href="<?php echo $view['router']->generate('sys_install_check') ?>">When you're ready to proceed, click here.</a>
	</p>
<?php else: ?>
	<p><strong>Please do this not and then refresh this page once you're ready to proceed.</strong></p>
<?php endif ?>
