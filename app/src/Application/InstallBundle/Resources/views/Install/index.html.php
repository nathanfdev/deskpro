<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Step 2: Server and Config Checks<?php $view['slots']->stop() ?>
<?php $failed = false ?>
<?php $failed_phpini = false ?>

<?php require(DP_ROOT.'/src/Application/InstallBundle/Resources/views/Install/server-checks-table.html.php') ?>

<?php if ($new_download): ?>
	<div class="alert-message block-message info" style="border: 3px solid #BDD1D7; margin-top: 35px; margin-bottom: 35px;">
		<strong>There is a newer version of DeskPRO available</strong><br />
		The version you are trying to install is version <var><?php echo $this_build ?></var>. A newer version, version <var><?php echo $new_build ?></var>, is available.
		<div class="alert-actions" style="margin-top: 10px;">
			<?php $new_download = 'http://www.deskpro.com/downloads/DeskPRO.zip'; ?>
			<a class="btn primary" href="<?php echo $new_download ?>">Click here to download the new version now</a>
			<br /><div style="font-size: 10px;">Download URL: <?php echo $new_download ?></div>
		</div>
	</div>
<?php endif ?>

<div id="loading_display" class="alert-message block-message">
	<center>Please wait while server checks are being performed. This should only take a few seconds.</center>
</div>

<div id="fatal_errors_display" class="alert-message block-message error" style="display: none">
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
		<a class="btn" href="<?php echo $view['router']->generate('install_checks') ?>">Refresh the page to re-run the checks</a>
	</div>
</div>

<div id="success_display" class="alert-message block-message success" style="display: none">
	<?php if ($new_download): ?>
		If you do not want to download the updated version of DeskPRO, you can continue on to the next step to install this outdated version.
	<?php else: ?>
		<strong>Everything looks okay.</strong> You are ready to continue
		to the next step.
	<?php endif ?>

	<div class="alert-actions submit-area">
		<a class="btn" href="<?php echo $view['router']->generate('install_check_urls') ?>" onclick="$(this).parent().addClass('clicked');">Go to step 3: Check URL rewriting</a>
		<span class="next-loading"></span>
	</div>
</div>

<!--
<?php echo htmlspecialchars(print_r($errors, true)) ?>
-->

<script type="text/javascript">
$(document).ready(function() {
	var is_fatal = <?php if ($is_fatal) echo 'true'; else echo 'false'; ?>;

	var baseurl = window.location.href;
	baseurl = baseurl.replace(/\/index\.php\/(.*?)$/, '');

	var dataurl = baseurl + '<?php echo $do_data_dir_check ?>/index.html';
	var reqtesturl = baseurl + '/index.php?_sys=check_http_method&x=' + ((new Date()).getTime());

	// fatal errors we can show errors right away
	if (is_fatal) {
		$('#fatal_errors_display').show();
		$('#success_display').hide();
		$('#loading_display').hide();

	// If all looks okay, we need ajax request to test GET/POST/DELETE/PUT requests
	} else {
		var check_methods = [];
		var check_done = 0;
		var check_status = {
			data_dir: true,
			http_get: false,
			http_post: false,
			http_put: false,
			http_delete: false
		};

		function showHttpStatus(m, pass) {
			$('#http_' + m + '_load').hide();

			if (pass) {
				$('#http_' + m + '_ok').show();
			} else {
				$('#http_' + m + '_fail').show();
				$('#http_method_error').show();
			}
		};

		function doneCheck() {
			check_done++;
			if (check_done == check_methods.length) {
				var any_error = false;
				$('#loading_display').hide();

				if (!check_status.data_dir) {
					$('#check_data_dir_web').show();
					any_error = true;
				}

				if (!check_status.http_get) {
					showHttpStatus('get', false);
					any_error = true;
				} else {
					showHttpStatus('get', true);
				}
				if (!check_status.http_post) {
					showHttpStatus('post', false);
					any_error = true;
				} else {
					showHttpStatus('post', true);
				}
				if (!check_status.http_put) {
					showHttpStatus('put', false);
					any_error = true;
				} else {
					showHttpStatus('put', true);
				}
				if (!check_status.http_delete) {
					showHttpStatus('delete', false);
					any_error = true;
				} else {
					showHttpStatus('delete', true);
				}


				if (any_error) {
					$('#success_display').hide();
					$('#fatal_errors_display').show();
				} else {
					$('#success_display').show();
				}
			}
		}

		<?php if (isset($do_data_dir_check) && $do_data_dir_check): ?>
		check_methods.push(function() {
			$.ajax({
				url: dataurl,
				dataType: 'text',
				cache: false,
				success: function (result) {
					if (result && result.indexOf('DESKPRO_READABLE_FILE') !== -1) {
						check_status.data_dir = false;
					}
				},
				complete: function () { doneCheck(); }
			});
		});
		<?php endif; ?>

		check_methods.push(function() {
			$.ajax({
				url: reqtesturl,
				dataType: 'text',
				type: "GET",
				cache: false,
				success: function (result) { if (result && result.indexOf('HTTP_METHOD_GET') !== -1) check_status.http_get = true; },
				complete: function () { doneCheck(); }
			});
		});

		check_methods.push(function() {
			$.ajax({
				url: reqtesturl,
				dataType: 'text',
				type: "POST",
				cache: false,
				success: function (result) { if (result && result.indexOf('HTTP_METHOD_POST') !== -1) check_status.http_post = true; },
				complete: function () { doneCheck(); }
			});
		});

		check_methods.push(function() {
			$.ajax({
				url: reqtesturl,
				dataType: 'text',
				type: "PUT",
				cache: false,
				success: function (result) { if (result && result.indexOf('HTTP_METHOD_PUT') !== -1) check_status.http_put = true; },
				complete: function () { doneCheck(); }
			});
		});

		check_methods.push(function() {
			$.ajax({
				url: reqtesturl,
				dataType: 'text',
				type: "DELETE",
				cache: false,
				success: function (result) { if (result && result.indexOf('HTTP_METHOD_DELETE') !== -1) check_status.http_delete = true; },
				complete: function () { doneCheck(); }
			});
		});

		for (var i = 0; i < check_methods.length; i++) {
			check_methods[i]();
		}
	}
});

</script>