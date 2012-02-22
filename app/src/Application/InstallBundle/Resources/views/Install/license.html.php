<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Step 2: License Agreement<?php $view['slots']->stop() ?>
<?php $failed = false ?>
<h3>License Agreement</h3>
<table class="bordered-table zebra-striped">
	<tbody>
		<tr>
			<td>
				<pre style="height: 300px; overflow: auto; font-family: monospace; border: none; margin: 0; padding: 0;"><?php echo $lictext ?></pre>
			</td>
		</tr>
	</tbody>
</table>

<div class="alert-message block-message warn" id="agreement_box">
	<label style="float: none; width: 100%;"><input type="checkbox" id="accept_check" /> I agree to the above license agreement</label>

	<div class="alert-actions">
		<a class="btn disabled" id="next_btn" href="<?php echo $view['router']->generate('install_verify_files') ?>">Go to step 3: Verify file integrity</a>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	$('#accept_check').on('click', function() {
		if (this.checked) {
			$('#agreement_box').removeClass('warn').addClass('success');
			$('#next_btn').removeClass('disabled');
		} else {
			$('#agreement_box').removeClass('success').addClass('warn');
			$('#next_btn').addClass('disabled');
		}
	});

	$('#next_btn').on('click', function(ev) {
		if ($(this).hasClass('disabled')) {
			ev.preventDefault();
		}
	});
});
</script>
