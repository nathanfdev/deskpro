<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Step 2: Server and Config Checks<?php $view['slots']->stop() ?>
<?php $failed = false ?>
<?php $failed_phpini = false ?>

<?php require(DP_ROOT.'/src/Application/InstallBundle/Resources/views/Install/server-checks-table.html.php') ?>

<style type="text/css" xmlns="http://www.w3.org/1999/html">
	#url_check_loading article {
		background: url(../../web/images/spinners/loading-big-circle.gif) no-repeat 50% 0;
		padding-top: 36px;
		text-align: center;
	}

	.faux-url {
		font-style: normal;
		padding: 1px 4px;
		font-family: Monaco, Courier, monospace;
		font-size: 11px;
	}
	.faux-url i {
		font-style: normal;
		margin-bottom: 1px;
	}

	i {
		font-style: normal;
	}

	table.layout { border: 0; margin: 0; }
	table.layout td { border: 0; margin: 0; padding: 2px; vertical-align: middle; }

	.codebox {
		font-family: Monaco, Courier, monospace;
		font-size: 11px;
		background-color: #EDEDED;
		border: 1px solid #929292;
		padding: 5px;
		margin: 2px 0 6px;
	}

	.kb-read-more.inline {
		float: none;
		display: inline-block;
		margin-left: 0;
		margin-top: 12px;
	}
</style>
<script type="text/javascript">
$(document).ready(function() {
	var baseurl = window.location.href;
	baseurl = baseurl.replace(/\/index\.php\/(.*?)$/, '');
	$('.dp-url-base').text(baseurl);

	var basepath = window.location.href;
	basepath = basepath.replace(/^(.*?)\/index\.php\/(.*?)$/, '$1');
	basepath = basepath.replace(/https?:\/\//, '');
	if (basepath.indexOf('/') == -1) {
		basepath = '/';
	} else {
		basepath = basepath.replace(/^(.*?)\/(.*?)$/, '/$2');
	}

	$('.dp-base-path').text(basepath);

	$.ajax({
		url: baseurl + '/__checkurlrewrite/path',
		timeout: 8000,
		dataType: 'html',
		complete: function() {
			$('#url_check_loading').hide();
		},
		error: function() {
			$('#url_check_off').show();
		},
		success: function(content) {
			if (content.indexOf('dp_check_okay') !== -1) {
				$('#url_check_pass').show();
			} else {
				$('#url_basepath_wrong').show();
			}
		}
	});
});
</script>

<h3>Checking for clean URL support</h3>
<table class="bordered-table">
	<tbody>
		<tr>
			<td>
				DeskPRO can use clean and short URLs when your server supports it. When clean URLs are enabled, the "index.php" segment
				of the URL is removed. For example:

				<table cellpadding="1" cellspacing="1" class="layout" style="margin-top: 8px">
					<tr>
						<td width="140">With clean URLs:</td>
						<td><em class="faux-url"><i class="dp-url-base"></i>/kb/1-example-article</em></td>
					</tr>
					<tr>
						<td>Without clean URLs:</td>
						<td><em class="faux-url"><i class="dp-url-base"></i>/index.php/kb/1-example-article</em></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="url_check_loading">
			<td>
				<article>Performing tests</article>
			</td>
		</tr>
		<tr id="url_check_pass" style="display: none">
			<td>
				<span class="label success" style="float:right">OK</span>
				Your server supports URL rewriting.
			</td>
		</tr>
		<tr id="url_check_off" style="display: none">
			<td>
				<span class="label notice" style="float:right">UNSUPPORTED</span>
				Your server is not capable of URL rewriting.
				<br />
				<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.url_rewriting') ?>" class="kb-read-more inline" target="_blank">Learn about enabling URL rewriting on your server &rarr;</a>
			</td>
		</tr>
		<tr id="url_basepath_wrong" style="display: none">
			<td>
				<span class="label important" style="float:right">PROBLEM</span>
				We have detected that your server supporst URL writing, but it is not configured properly. This can sometimes happen if the document root of your
				site and the directory that DeskPRO is being served from are different (for example, if you use Apache Aliases).
				<br /><br />

				To fix this problem, you need to make a small change to the <em class="faux-url">.htaccess</em> file in the root DeskPRO directory.
				Find the line that looks like this;
				<div class="codebox">#RewriteBase /deskpro</div>
				You need to remove the leading "#" and then change the path to the root URL of DeskPRO:
				<div class="codebox">RewriteBase <i class="dp-base-path"></i></div>

				<br />
				This step is optional. If you continue without correcting the probelm, DeskPRO will still function perfectly without the clean URLs. You can go back
				and enable clean URLs at any time.

				<br />
				<a href="<?php echo \Application\DeskPRO\App::get('deskpro.service_urls')->get('dp.kb.install.url_rewriting') ?>" class="kb-read-more inline" target="_blank">Learn more about URL rewriting &rarr;</a>
			</td>
		</tr>
	</tbody>
</table>

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
		<?php if ($new_download): ?>
			If you do not want to download the updated version of DeskPRO, you can conitnue on to the next step to install this outdated version.
		<?php else: ?>
			<strong>Everything looks okay.</strong> You are ready to continue to continute
			to the next step.
		<?php endif ?>

		<div class="alert-actions">
			<a class="btn" href="<?php echo $view['router']->generate('install_verify_files') ?>" onclick="this.onclick=function(){return false;};">Go to step 3: Verify file integrity</a>
		</div>
	</div>
<?php endif ?>

<!--
<?php echo htmlspecialchars(print_r($errors, true)) ?>
-->
