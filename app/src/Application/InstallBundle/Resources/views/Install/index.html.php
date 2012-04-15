<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Step 2: Server and Config Checks<?php $view['slots']->stop() ?>
<?php $failed = false ?>
<?php $failed_phpini = false ?>

<?php require(DP_ROOT.'/src/Application/InstallBundle/Resources/views/Install/server-checks-table.html.php') ?>

<?php if ($is_fatal): ?>
	<div class="alert-message block-message error">
		<strong>There were errors</strong>, as noted above, that must be fixed before you
		can install DeskPRO. You cannot continue with the installation until the problems
		above have been fixed.

		<?php if ($db_failed && $can_write_config): ?>
			<br /><br />
			<a href="<?php echo $view['router']->generate('install_configedit') ?>" style="color: #0069D6;">Go back to the config editor</a> and update your database details then try again.
			<br /><br />
		<?php endif ?>

		<?php if ($ini_path and $failed_phpini): ?>
			<br /><br />
			We have detected the path to your php.ini file at <code><?php echo $ini_path ?></code>. You will need to edit
			this file to enable the missing extensions. Depending on your server, you may also need to download and compile the extensions
			first.
			<br /><br />
		<?php endif ?>

		<div class="alert-actions">
			<a class="btn" href="<?php echo $view['router']->generate('install_checks') ?>">Refresh the page to re-run he checks</a>
		</div>
	</div>
<?php else: ?>
	<div class="alert-message block-message success">
		<strong>Everything looks okay.</strong> You are ready to continue to continute
		to the next step.

		<div class="alert-actions">
			<a class="btn" href="<?php echo $view['router']->generate('install_verify_files') ?>">Go to step 3: Verify file integrity</a>
		</div>
	</div>
<?php endif ?>

<!--
<?php echo htmlspecialchars(print_r($errors, true)) ?>
-->
