<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Step 1: Server and Config Checks<?php $view['slots']->stop() ?>
<?php $failed = false ?>
<h3>Server Checks</h3>
<table class="bordered-table zebra-striped">
	<tbody>
		<tr>
			<td>
				<?php if (!isset($errors['php_version'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				PHP version is &gt;= 5.3.2
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires PHP 5.3.2. You have <?php echo phpversion() ?>
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['pdo_ext']) && !isset($errors['pdo_mysql_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				PDO enabled and has the MySQL driver installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires the PDO extension and the MySQL driver
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['mbstring_ext']) && !isset($errors['mbstring_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				mbstring extension installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires the mbstring extension
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['json_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				json_encode extension installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires the json_encode extension
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['session_start'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				session extension installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires the session extension
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['ctype_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				ctype extension installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires ctype extension
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['tokenizer_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				tokenizer extension installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires tokenizer extension
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['image_manip'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				An image manipulation extension is installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires one of the following extensions: Imagick, Gmagick or GD
					</div>
				<?php endif ?>
			</td>
		</tr>


		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['memory_limit'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				PHP Memory Limit (at least 128 MB)
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires PHP's memory_limit option to be at least 128 MB.
					</div>
				<?php endif ?>
			</td>
		</tr>
	</tbody>
</table>

<?php if ($has_db_checks): ?>
	<h3>Configuration and Database Checks</h3>
	<?php if(!$has_config): ?>
		<div class="alert-message block-message error">
			<strong>You don't have a config file</strong>!
			<p>
				In the root directory, rename <code>config.new.php</code> to <code>config.php</code>
				and edit the values inside with your database connection details. Once you have done this,
				re-run this page.
			</p>
			<div class="alert-actions">
				<a class="btn" href="<?php echo $view['router']->generate('install') ?>">I have made my config.php file, refresh this page</a>
			</div>
		</div>
	<?php else: ?>
		<table class="bordered-table zebra-striped">
			<tbody>
				<tr>
					<td>
						<span class="label success" style="float:right">OK</span>
						Config file exists and is readable
					</td>
				</tr>
				<tr>
					<td>
						<?php $failed = false ?>
						<?php if (!isset($errors['db_connect'])): ?>
							<span class="label success" style="float:right">OK</span>
						<?php else: $failed = true; ?>
							<span class="label important" style="float:right">FAIL</span>
						<?php endif ?>
						Checking database connection
						<?php if ($failed): ?>
							<div class="alert-message block-message error">
								A database connection could not be established. Check your config.php to make sure
								the details you entered are correct.
								<p><code><?php echo $errors['db_connect']['message'] ?></code></p>
							</div>
						<?php endif ?>
					</td>
				</tr>

				<?php if (!isset($errors['db_connect'])): ?>
					<tr>
						<td>
							<?php $failed = false ?>
							<?php if (!isset($errors['db_version'])): ?>
								<span class="label success" style="float:right">OK</span>
							<?php else: $failed = true; ?>
								<span class="label important" style="float:right">FAIL</span>
							<?php endif ?>
							Checking MySQL version
							<?php if ($failed): ?>
								<div class="alert-message block-message error">
									DeskPRO requires MySQL v5.5. You need to update your version of MySQL.
								</div>
							<?php endif ?>
						</td>
					</tr>

					<tr>
						<td>
							<?php $failed = false ?>
							<?php if (!isset($errors['db_no_innodb'])): ?>
								<span class="label success" style="float:right">OK</span>
							<?php else: $failed = true; ?>
								<span class="label important" style="float:right">FAIL</span>
							<?php endif ?>
							Checking for InnoDB Engine
							<?php if ($failed): ?>
								<div class="alert-message block-message error">
									Your MySQL server does not support the InnoDB engine.
								</div>
							<?php endif ?>
						</td>
					</tr>

					<tr>
						<td>
							<?php $failed = false ?>
							<?php if (!isset($errors['db_not_empty'])): ?>
								<span class="label success" style="float:right">OK</span>
							<?php else: $failed = true; ?>
								<span class="label important" style="float:right">FAIL</span>
							<?php endif ?>
							Checking for existing tables
							<?php if ($failed): ?>
								<div class="alert-message block-message error">
									Existing tables were detected in your database. DeskPRO should be installed
									into a new, fresh database.
								</div>
							<?php endif ?>
						</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	<?php endif ?>
<?php endif ?>

<?php if ($is_fatal): ?>
	<div class="alert-message block-message error">
		<strong>There were errors</strong>, as noted above, that must be fixed before you
		can install DeskPRO. You cannot continue with the installation until the problems
		above have been fixed.

		<div class="alert-actions">
			<a class="btn" href="<?php echo $view['router']->generate('install') ?>">Refresh the page to re-run he checks</a>
		</div>
	</div>
<?php else: ?>
	<div class="alert-message block-message success">
		<strong>Everything looks okay.</strong> You are ready to continue to continute
		to the next step.

		<div class="alert-actions">
			<a class="btn" href="<?php echo $view['router']->generate('install_verify_files') ?>">Go to step 2: Verify file integrity</a>
		</div>
	</div>
<?php endif ?>
