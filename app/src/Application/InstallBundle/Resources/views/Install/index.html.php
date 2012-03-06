<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Step 2: Server and Config Checks<?php $view['slots']->stop() ?>
<?php $failed = false ?>
<?php $failed_phpini = false ?>
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
				Check that the <a href="http://php.net/">PHP</a> version is &gt;= 5.3.2
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires PHP 5.3.2. You have <?php echo phpversion() ?>
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php if (!isset($errors['config']) && !isset($errors['config_values'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check for valid config.php
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						<?php if (isset($errors['config'])): ?>
							/config.php is missing. Copy /config.new.php and edit it to add your database settings.
						<?php elseif (!isset($errors['config_values'])): ?>
							/config.php exists but it does not contain the required settings. You should copy /config.new.php and edit it to add your database settings.
						<?php endif ?>
					</div>
				<?php endif ?>
			</td>
		</tr>


		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['pdo_ext']) && !isset($errors['pdo_mysql_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; $failed_phpini = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that the <a href="http://php.net/manual/en/pdo.installation.php">PDO extension</a> is enabled and has the MySQL driver installed
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
				<?php if (!isset($errors['iconv_ext']) && !isset($errors['iconv_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; $failed_phpini = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that the <a href="http://php.net/manual/en/iconv.installation.php">iconv extension</a> is installed
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires the iconv extension to be installed and enabled
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['json_ext'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; $failed_phpini = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that the <a href="http://php.net/manual/en/json.installation.php">json_encode extension</a> is installed
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
				<?php else: $failed = true; $failed_phpini = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that the <a href="http://php.net/manual/en/session.installation.php">session extension</a> is installed
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
				<?php else: $failed = true; $failed_phpini = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that the <a href="http://php.net/manual/en/ctype.installation.php">ctype extension</a> is installed
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
				<?php else: $failed = true; $failed_phpini = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that the <a href="http://php.net/manual/en/tokenizer.installation.php">tokenizer extension</a> is installed
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
				<?php else: $failed = true; $failed_phpini = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that an image manipulation extension is installed (<a href="http://php.net/manual/en/imagick.installation.php">Imagick</a>, <a href="http://php.net/manual/en/gmagick.installation.php">Gmagick</a>, or <a href="http://php.net/manual/en/image.installation.php">GD</a>)
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
				Check that PHP's <a href="http://php.net/manual/en/ini.core.php#ini.memory-limit">memory limit</a> is at least 128 MB
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						DeskPRO requires PHP's memory_limit option to be at least 128 MB. Edit your php.ini file <?php if ($ini_path): ?>(<code><?php echo $ini_path ?></code>)<?php endif ?> to increase the limit.
					</div>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php $failed = false ?>
				<?php if (!isset($errors['logs_write'])): ?>
					<span class="label success" style="float:right">OK</span>
				<?php else: $failed = true; ?>
					<span class="label important" style="float:right">FAIL</span>
				<?php endif ?>
				Check that the logs directory is writable
				<?php if ($failed): ?>
					<div class="alert-message block-message error">
						The logs directory (<?php echo $logs_dir_info ?>) must exist and be writable.
					</div>
				<?php endif ?>
			</td>
		</tr>
	</tbody>
</table>

<?php if ($has_db_checks): ?>
<h3>Database Checks</h3>
	<table class="bordered-table zebra-striped">
		<tbody>
			<tr>
				<td>
					<?php $failed = false ?>
					<?php if (!isset($errors['db_connect'])): ?>
						<span class="label success" style="float:right">OK</span>
					<?php else: $failed = true; ?>
						<span class="label important" style="float:right">FAIL</span>
					<?php endif ?>
					Check database connection (<?php echo $db_config['user'] ?>@<?php echo $db_config['host'] ?>/<?php echo $db_config['dbname'] ?>)
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
						Check MySQL version is &gt;= 5.5
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
						Check for InnoDB Engine
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
						Check for existing tables
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

<?php if ($is_fatal): ?>
	<div class="alert-message block-message error">
		<strong>There were errors</strong>, as noted above, that must be fixed before you
		can install DeskPRO. You cannot continue with the installation until the problems
		above have been fixed.

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
