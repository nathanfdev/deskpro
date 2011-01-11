<?php $view->extend('DevBundle::layout.php') ?>
<?php $view['slots']->start('head') ?>
<script type="text/javascript">
var Upgrader = {
	versions: [
		'<?php echo implode("',\n\t\t'", \Orb\Util\Arrays::flattenToIndex($behind_versions, 'version_id')) ?>'
	],

	upgradeUrl: '<?php echo $view['router']->generate('dev_build_upgrade_do') ?>',

	init: function() {
		$('#do_next_upgrade').click(function() {
			Upgrader.nextUpgrade();
			$(this).attr('disabled', true);
		});
	},

	currentUpgradeId: null,

	nextUpgrade: function() {
		var nextId = this.versions.shift();
		if (nextId) {
			this.startUpgrade(nextId);
		}
	},

	startUpgrade: function(id) {

		$('#do_next_upgrade').attr('disabled', true);

		this.currentUpgradeId = id;
		var tr = $('#version_' + id);
		var td_result = $('td.result', tr);
		var td_status = $('td.status', tr);

		tr.removeClass('done').removeClass('failure').removeClass('success');
		tr.addClass('running');
		td_status.html('running');
		td_result.html('<button>Toggle Results</button><iframe src="' + this.upgradeUrl + '?version_id='+id+'" width="100%" height="500"></iframe>');
	},

	upgradeDone: function(id, status) {
		if (this.currentUpgradeId != id) {
			alert('Uh oh, the upgrade step reported back the wrong version. Is something wrong?');
		}
		this.currentUpgradeId = null;

		var tr = $('#version_' + id);
		var td_result = $('td.result', tr);
		var td_status = $('td.status', tr);

		tr.removeClass('running');
		tr.addClass('done');

		$('button', td_result).click(function() {
			$('iframe', $(this).parent()).toggle();
		});

		if (status) {
			tr.addClass('success');
			td_status.html('done');
			this.nextUpgrade();
		} else {
			tr.addClass('failure');
			td_status.html('failed <button>retry</button>');
			$('button', td_status).click(function() {
				$(this).remove();
				Upgrader.startUpgrade(id);
			});
			$('#do_next_upgrade').attr('disabled', false);
		}
	}
};

$(document).ready(function() {
	Upgrader.init();
});
</script>
<style type="text/css">
	tr.version-row.running td.status { color: #1F66FF; }
	tr.version-row.done.failure td.status { color: #FF1F1F; }
	tr.version-row.done.success td.status { color: #27BF1F; }

	tr.version-row.running td.result button { display: none; }
	tr.version-row.done.success td.result iframe { display: none; }
	tr.version-row.done td.result button { display: block; }
</style>
<?php $view['slots']->stop() ?>

<h1>Performing Upgrades</h1>
<button id="do_next_upgrade">Click here to start the next upgrade</button>
<br />
<br />

<table width="100%">
	<?php foreach ($behind_versions as $behind): ?>
		<tr class="version-row" id="version_<?php echo $behind['version_id'] ?>">
			<td class="title" width="150"><?php echo $behind['version'] ?><br ><small><?php echo $behind['version_id'] ?></small></td>
			<td class="result">-</td>
			<td class="status" width="100">waiting</td>
		</tr>
	<?php endforeach ?>
</table>
