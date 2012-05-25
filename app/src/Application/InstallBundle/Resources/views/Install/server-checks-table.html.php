<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<style type="text/css">
	.kb-read-more {
		font-size: 11px;
		float: right;
		margin: -10px -8px 10px 30px;

		display: block;
		background-color: #fff;
		line-height: 100%;
		padding: 5px 8px 5px 22px;

		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		-moz-background-clip: padding; -webkit-background-clip: padding-box; background-clip: padding-box;

		background: #fff url(../../web/images/agent/icons/small-light-on.png) no-repeat 7px 50%;
		border: 1px solid #aaa;
	}
	.kb-read-more:hover {
		border: 1px solid #2B629B;
		text-decoration: none;
		color: #1E4C7A;
	}
</style>

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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_php_version') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			DeskPRO requires PHP 5.3.2. You have <?php echo phpversion() ?>.
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_config_missing') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			/config.php is missing. Copy /config.new.php and edit it to add your database settings.
			<?php elseif (!isset($errors['config_values'])): ?>
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_config_invalid') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_pdo_ext') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_iconv_ext') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			DeskPRO requires the iconv extension to be installed and enabled.
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_json_ext') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			DeskPRO requires the json_encode extension.
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_session_ext') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			DeskPRO requires the session extension.
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_ctype_ext') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			DeskPRO requires ctype extension.
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_tokenizer_ext') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			DeskPRO requires tokenizer extension.
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_image_manip') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			DeskPRO requires one of the following extensions: Imagick, Gmagick or GD.
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_memory_limit') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
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
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_logs_dir') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
			The logs directory (<?php echo $logs_dir_info ?>) must exist and be writable.
		</div>
			<?php if ($is_default_logs_dir): ?>
				<p>
					We recommend making the entire data/ directory (and everything under it) writable. Some features you may want to use later will require write access, so we recommend making the
					changes now for easier setup.
				</p>
			<?php endif ?>
			<?php if (strpos(strtoupper(PHP_OS), 'WIN') === 0): ?>
			<?php else: ?>
				<p>
					On Linux systems, you can run this command from the terminal:
					<code>chmod -R 0777 <?php if ($is_default_logs_dir): ?><?php echo DP_WEB_ROOT ?>/data<?php else: ?><?php echo $logs_dir_info_full ?><?php endif ?></code>
				</p>
			<?php endif ?>
		<?php endif ?>
	</td>
</tr>

<tr>
	<td>
		<?php $failed = false ?>
		<?php if (!isset($errors['openssl_ext'])): ?>
		<span class="label success" style="float:right">OK</span>
		<?php else: $failed = true; ?>
		<span class="label notice" style="float:right">RECOMMENDED</span>
		<?php endif ?>
		Checking for the <a href="http://www.php.net/manual/en/openssl.installation.php">OpenSSL</a> extension
		<?php if ($failed): ?>
		<div class="alert-message block-message info">
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_openssl') ?>" class="kb-read-more" target="_blank">Read more about this</a>
			We recommend installing the OpenSSL extension so you can use web resources that require a secure connection (such as Gmail or Google Apps, secure email servers, Facebook or Twitter).
		</div>
		<?php endif ?>
	</td>
</tr>

<tr>
	<td>
		<?php $failed = false ?>
		<?php if (!isset($errors['apc_check'])): ?>
		<span class="label success" style="float:right">OK</span>
		<?php else: $failed = true; ?>
		<span class="label notice" style="float:right">RECOMMENDED</span>
		<?php endif ?>
		Checking for the <a href="http://www.php.net/manual/en/apc.installation.php">APC extension</a>
		<?php if ($failed): ?>
		<div class="alert-message block-message info">
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_apc') ?>" class="kb-read-more" target="_blank">Read more about this</a>
			We recommend installing the <a href="http://www.php.net/manual/en/apc.installation.php">APC extension</a> to dramatically improve performance.
		</div>
		<?php endif ?>
	</td>
</tr>

<tr>
	<td>
		<?php $failed = false ?>
		<?php if (!isset($errors['magic_quotes_gpc_check'])): ?>
		<span class="label success" style="float:right">OK</span>
		<?php else: $failed = true; ?>
		<span class="label notice" style="float:right">RECOMMENDED</span>
		<?php endif ?>
		Checking if <a href="http://www.php.net/manual/en/security.magicquotes.disabling.php">magic_quotes_gpc</a> is disabled
		<?php if ($failed): ?>
		<div class="alert-message block-message info">
			<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_magic_quotes') ?>" class="kb-read-more" target="_blank">Read more about this</a>
			We recommend disabling <code>magic_quotes_gpc</code> in your php.ini for a small performance improvement.
			(<?php if ($ini_path): ?>Your php.ini file is located at <code><?php echo $ini_path ?></code><?php endif ?>)
		</div>
		<?php endif ?>
	</td>
</tr>
</tbody>
</table>

<?php $db_failed = false; ?>
<?php if ($has_db_checks): ?>
<h3>Database Checks</h3>
<table class="bordered-table zebra-striped">
	<tbody>
	<tr>
		<td>
			<?php $failed = false ?>
			<?php if (!isset($errors['db_connect'])): ?>
			<span class="label success" style="float:right">OK</span>
			<?php else: $failed = true; $db_failed = true; ?>
			<span class="label important" style="float:right">FAIL</span>
			<?php endif ?>
			Check database connection (<?php echo $db_config['user'] ?>@<?php echo $db_config['host'] ?>/<?php echo $db_config['dbname'] ?><?php if (!$failed and $did_create_db): ?>, the database was automatically created for you.<?php endif ?>)
			<?php if ($failed): ?>
			<div class="alert-message block-message error">
				<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_db_connect') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
				A database connection could not be established. Check your config.php to make sure
				the details you entered are correct.
				<p><code><?php echo $errors['db_connect']['message'] ?></code></p>
			</div>
			<?php endif; ?>
		</td>
	</tr>

		<?php if (!isset($errors['db_connect'])): ?>
	<tr>
		<td>
			<?php $failed = false ?>
			<?php if (!isset($errors['db_version'])): ?>
			<span class="label success" style="float:right">OK</span>
			<?php else: $failed = true; $db_failed = true; ?>
			<span class="label important" style="float:right">FAIL</span>
			<?php endif ?>
			Check MySQL version is &gt;= 5.0
			<?php if ($failed): ?>
			<div class="alert-message block-message error">
				<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_db_version') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
				DeskPRO requires MySQL v5.0. You need to update your version of MySQL.
			</div>
			<?php endif ?>
		</td>
	</tr>

	<tr>
		<td>
			<?php $failed = false ?>
			<?php if (!isset($errors['db_no_innodb'])): ?>
			<span class="label success" style="float:right">OK</span>
			<?php else: $failed = true; $db_failed = true; ?>
			<span class="label important" style="float:right">FAIL</span>
			<?php endif ?>
			Check for InnoDB Engine
			<?php if ($failed): ?>
			<div class="alert-message block-message error">
				<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_db_no_innodb') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
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
			<?php else: $failed = true; $db_failed = true; ?>
			<span class="label important" style="float:right">FAIL</span>
			<?php endif ?>
			Ensuring empty database
			<?php if ($failed): ?>
			<div class="alert-message block-message error">
				<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.error_db_not_empty') ?>" class="kb-read-more" target="_blank">Read more about fixing this error</a>
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