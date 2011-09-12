<?php $view->extend('SysBundle::layout.html.php') ?>
<?php $view['slots']->start('head') ?>
<script tyep="text/css">
$(document).ready(function() {
	$('td.results > button').click(function() {
		$('div.details', $(this).parent()).toggle();
	});
});
</script>
<style type="text/css">
	tr.version-row.running td.status { color: #1F66FF; }
	tr.version-row.done.failure td.status { color: #FF1F1F; }
	tr.version-row.done.success td.status { color: #27BF1F; }

	tr.version-row.done td.result button { display: block; }
</style>
<?php $view['slots']->stop() ?>
<h1>3. Create Tables</h1>

<table width="100%">
	<?php foreach ($all_sql as $k => $sql): ?>
		<?php
			$error = false;
			try {
				$db->executeQuery($sql);
			} catch (\Exception $e) {
				$error = $e->getMessage();
			}
		?>
		<tr class="version-row done <?php if ($error) echo 'failure'; else echo 'success'; ?>">
			<td width="25"><?php echo $k ?></td>
			<td class="results">
				<button>Toggle Details</button>
				<div class="details" style="display:none">
					<textarea style="width: 95%; height: 80px; font-family: 'Monaco', 'Courier New', monospace;"><?php echo $view->escape($sql) ?></textarea>
					<div class="errmsg"><?php echo $error ?></div>
				</div>
			</td>
			<td class="status"><?php if ($error): ?>failed<?php else: ?>success<?php endif ?></td>
		</tr>
	<?php endforeach ?>
</table>

<p>
	<a href="<?php echo $view['router']->generate('sys_install_createdata') ?>">Click here to continue: install default data</a>
</p>
