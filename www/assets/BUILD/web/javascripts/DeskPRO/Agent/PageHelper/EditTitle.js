Orb.createNamespace('DeskPRO.Agent.PageHelper');
DeskPRO.Agent.PageFragment.Page.EditTitle = new Orb.Class({
	initialize: function(page, saveUrl, data) {
		var namef       = page.getEl('showname');
		var editName    = page.getEl('editname');
		var startBtn    = page.getEl('editname_start');
		var stopBtn     = page.getEl('editname_end');
		var codeid      = page.getMetaData('obj_code') || null;

		var startEditable = function() {
			namef.hide();
			editName.show();
			startBtn.hide();
			stopBtn.show();
		};

		var stopEditable = function() {
			var nametxt = editName.find('input').first();
			var setName = $.trim(nametxt.val());

			if(!setName) {
				return;
			}

			editName.hide();
			startBtn.show();
			namef.show();
			stopBtn.hide();
			namef.text(setName);

			var postData = data ? Array.slice(data) : [];
			postData.push({
				name: 'action',
				value: 'title'
			});
			postData.push({
				name: 'title',
				value: setName
			});

			if (codeid) {
				$('span.obj-title-' + codeid).text(setName);
			}

			$.ajax({
				url: saveUrl,
				type: 'POST',
				data: postData,
				success: function(retData) {
					if (page.handleUnloadRevisions) {
						page.handleUnloadRevisions(retData.revision_id);
					}
					if (page.newTitleCallback) {
            page.newTitleCallback();
					}
				}
			});
		};

		namef.on('dblclick', startEditable).on('keypress', function(ev) {
			if (ev.keyCode == 13 /* enter key */) {
				ev.preventDefault();
				stopEditable();
			}
		});
		page.getEl('editname_start').on('click', startEditable);
		page.getEl('editname_end').on('click', stopEditable);
	}
});