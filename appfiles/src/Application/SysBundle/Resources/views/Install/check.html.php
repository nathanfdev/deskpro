<?php $view->extend('SysBundle::layout.html.php') ?>

<h1>2. Checks</h1>

<table width="100%">
	<tr class="alt">
		<td width="100">Check</td>
		<td>Description</td>
		<td width="100">Status</td>
	</tr>
	<tr>
		<td>php_version</td>
		<td>DeskPRO4 requires PHP 5.3.2 or later</td>
		<td><?php echo $checks['php_version'] ? 'okay' : 'PROBLEM' ?></td>
	</tr>
	<tr>
		<td>ext_pdo_mysql</td>
		<td>DeskPRO4 requires the <a href="http://php.net/pdo"><var>pdo</var> and <var>pdo_mysql</var></a> extensions.</td>
		<td><?php echo $checks['ext_pdo_mysql'] ? 'okay' : 'PROBLEM' ?></td>
	</tr>
	<tr>
		<td>writable_cache</td>
		<td>
			<var><?php echo DP_ROOT ?>/sys/cache</var> must be writable by the web server.
		</td>
		<td><?php echo $checks['writable_cache'] ? 'okay' : 'PROBLEM' ?></td>
	</tr>
	<tr>
		<td>writable_logs</td>
		<td>
			<var><?php echo DP_ROOT ?>/sys/logs</var> must be writable by the web server.
		</td>
		<td><?php echo $checks['writable_logs'] ? 'okay' : 'PROBLEM' ?></td>
	</tr>
	<tr>
		<td>writable_version</td>
		<td>
			<var><?php echo DP_ROOT ?>/sys/VERSION</var> is a single file that must be writable. Either make the sys directory
			writable, or create the VERSION file and make it writable.
		</td>
		<td><?php echo $checks['writable_version'] ? 'okay' : 'PROBLEM' ?></td>
	</tr>
	<tr>
		<td>config</td>
		<td>
			<var><?php echo DP_ROOT ?>/config.php</var> must contain valid database details.
			<?php if (!empty($checks_msg['config'])): ?>Error info: <?php echo $checks_msg['config'] ?><?php endif ?>
		</td>
		<td><?php echo $checks['config'] ? 'okay' : 'PROBLEM' ?></td>
	</tr>
</table>


<?php if (!$is_error): ?>
	<p>Looks like all checks pass.</p>
	<p>
		<a href="<?php echo $view['router']->generate('sys_install_createtables') ?>">When you're ready to proceed, click here.</a>
	</p>
<?php else: ?>
	<p><strong>Please fix the identified problems then refresh this page.</strong></p>
<?php endif ?>
