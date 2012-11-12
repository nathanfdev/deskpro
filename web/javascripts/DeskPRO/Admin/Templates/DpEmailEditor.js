function DpEmailEditor() {
	var codeHints = new DpCodeHints();
	var activeEditorArea = null;

	//##################################################################################################################
	//# Init the editors
	//##################################################################################################################

	$('.template-editor-wrap').each(function() {

		$(this).on('click', function(ev) {
			ev.stopPropagation();
		});

		var textarea  = $(this).find('textarea.template-editor');
		var codeCm = CodeMirror.fromTextArea(textarea.get(0), {
			mode: textarea.data('mode') || 'htmlmixed',
			lineNumbers: true,
			indentWithTabs: true,
			onCursorActivity: function() {
				codeHints.show(codeCm, textarea);
			},
			onFocus: function() {
				// When we switch focus to new textarea, close
				// possibly open hints from old one
				codeHints.hide();
				activeEditorArea = textarea;
			}
		});

		textarea.data('cm', codeCm);
	});

	// When clicking off, close any open tops
	$(document).on('click', function(ev) {
		var targetTip = $(ev.target).closest('.code-tip');
		if (targetTip[0]) {
			return;
		}
		codeHints.hide();
	});

	//##################################################################################################################
	//# Saving / reverting current template
	//##################################################################################################################

	var saveCtrl = $('#save_control');
	saveCtrl.find('button.save-trigger').on('click', function(ev) {
		ev.preventDefault();

		var subject = $('textarea.subject').val();
		var body    = $('textarea.template').val();

		var code = "<dp:subject>" + subject + "</dp:subject>\n" + body;

		saveCtrl.addClass('loading');
		$.ajax({
			url: BASE_URL + 'admin/templates/save-template.json',
			context: this,
			type: 'POST',
			data: {
				name: '{{ name }}',
				code: code
			},
			complete: function() {
				saveCtrl.removeClass('loading');
			},
			success: function(data) {
				if (data.error) {
					alert(data.error_message + "\n\nLine: " + data.error_line);
					return;
				}
			}
		});
	});

	saveCtrl.find('button.revert-trigger').on('click', function(ev) {
		ev.preventDefault();

		saveCtrl.addClass('loading');
		$.ajax({
			type: 'POST',
			url: BASE_URL + 'admin/templates/revert-template.json?name={{ name }}',
			success: function() {
				window.location.reload(false);
			}
		});
	});

	//##################################################################################################################
	//# Adding new phrases
	//##################################################################################################################

	var addOverlayEl = $('#add_phrase_overlay');
	var addOverlay = new DeskPRO.UI.Overlay({
		contentElement: addOverlayEl,
		onBeforeOverlayOpened: function() {
			addOverlayEl.find('textarea.custom_phrase, input.phrase_id').val('');
		}
	});

	addOverlayEl.find('button.save-trigger').on('click', function(ev) {
		ev.preventDefault();
		$.ajax({
			url: $(this).data('add-url'),
			type: 'POST',
			data: addOverlayEl.find('textarea, input').serializeArray(),
			dataType: 'json',
			success: function(data) {
				addOverlay.close();

				var cm = activeEditorArea.data('cm').replaceSelection('{{ phrase(\'' + data.phrase_id + '\') }}');
			}
		});
	});

	$('.template-toolbar .new-phrase').on('click', function() {
		addOverlay.open();
		activeEditorArea = $(this).closest('.template-edit-row').find('textarea.template-editor');
	});
}