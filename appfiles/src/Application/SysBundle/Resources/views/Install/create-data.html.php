<?php $view->extend('SysBundle::layout.html.php') ?>
<h1>4. Create Data</h1>

<textarea style="width: 95%; height: 200px; font-family: 'Monaco', 'Courier New', monospace;"><?php echo $results ?></textarea>

<?php if ($error): ?>
	<strong>Error</strong>
	<p><?php echo $error ?></p>
<?php else: ?>
	<h2>Done</h2>
	Initial data inserted successfully.

	<p>An initial agent/admin was created for you:</p>
	<table>
		<tr>
			<td>Email</td>
			<td>admin@example.com</td>
		</tr>
		<tr>
			<td>Password</td>
			<td>pass</td>
		</tr>
		<tr>
			<td>Log In</td>
			<td>
				Log in at <strong>/index_dev.php/agent/</strong>
			</td>
		</tr>
	</table>
<?php endif ?>
