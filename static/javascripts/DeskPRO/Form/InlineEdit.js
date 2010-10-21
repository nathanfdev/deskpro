Orb.createNamespace('DeskPRO.Form');

DeskPRO.Form.InlineEdit = new Class({
	Implements: Options,
	
	/**
	 * Hash of options
	 * @var {Object}
	 */
	options: {
		baseElement: window.document,
		editableClass: 'editable',
		autoSave: false,
		ajax: {
			timeout: 20000,
			type: 'POST',
			url: ''
		}
	},
	
	activeEdits: [],
	sendingEdits: {},
	
	documentClickSubmitOn: false,
	
	initialize: function (options) {
		this.setOptions(options);

		var sel = '.' + this.options['editableClass'];
		var self = this;
		$(sel, this.options['baseElement']).each(function() { self.initEditable(this); });
		
		$(document).click(function(ev) {
			self.handleDocumentClick(ev);
		})
		
		$(document).keydown(function(ev) {
			// Escape key
			if (ev.keyCode == 27) {
				self.closeEditables();
			}
		});
	},
	
	/**
	 * Initialize an editable by attaching new triggers
	 *
	 * @param {HTMLElement} el
	 */
	initEditable: function(el) {
		var self = this;
		
		var j_el = $(el);
		
		if (j_el.is('.parent-trigger')) {
			var parent = j_el.parent();
			parent.dblclick(function() {
				self.startEditable(j_el);
			});
		} else {
			j_el.dblclick(function() { self.startEditable(this); });
		}
	},
	
	
	
	handleDocumentClick: function(ev) {
		if (!this.documentClickSubmitOn) {
			return;
		}
		
		// Dont listen if the click was inside the editable area
		if ($(event.target).parents().is('.editable')) {
			return;
		}
		
		this.submitOpen();
		this.documentClickSubmitOn = false;
	},
	
	
	
	/**
	 * Start editing an element. This removes the rendered value and replaces
	 * it with the form fields.
	 *
	 * @param {HTMLElement} el
	 */
	startEditable: function(editable) {

		editable = $(editable);
		
		// 1. Detatch (but dont remove) rendered elements from DOM
		// 2. Move form from hidden container to editable container

		var rendered_els = editable.children();
		if (!rendered_els.size()) {
			editable.wrapInner('<div />');
			rendered_els = editable.children();
		}

		var form_elements = $('#' + $(editable).data('editable-for'));
		var form_elements_container = form_elements.parent();

		rendered_els.fadeOut('fast', function() {
			rendered_els.detach();
			form_elements.addClass('editable-fields-on').hide().appendTo(editable).fadeIn('fast');
		});
	
		var editinfo = {
			'editable': editable,
			'rendered_els': rendered_els,
			'form_elements': form_elements,
			'form_elements_container': form_elements_container
		};
		
		this.documentClickSubmitOn = true;
		this.activeEdits.push(editinfo);
	},
	
	submitOpen: function() {
		var data = $('.editable-fields-on :input, .editable-ajax-data :input', this.options['baseElement']).serializeArray();
		
		var is_multi = this.activeEdits.length;
		
		var sending_edits = [];
		
		// Move all open to pending
		var editinfo = null;
		while (editinfo = this.activeEdits.pop()) {
			this.setEditinfoLoading(editinfo);
			sending_edits.push(editinfo);
		}
		
		var ajax_id = Orb.uuid();
		this.sendingEdits[ajax_id] = sending_edits;
		
		var self = this;
		var ajax_options = Object.merge({
			success: function(data, textStatus, XMLHttpRequest) {
				self.handleAjaxSuccess(ajax_id, data);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				self.handleAjaxFailure(ajax_id);
			},
			dataType: 'json',
			data: data
		}, this.options['ajax']);
		
		console.log('ajax-save: %s', ajax_options.url);
		console.log('ajax-save data: %o', ajax_options.data);
		
		$.ajax(ajax_options);
	},
	
	handleAjaxSuccess: function(ajax_id, data) {
	
		var all_sending_edits = this.sendingEdits[ajax_id];
		delete this.sendingEdits[ajax_id];
		
		var sending_edit = null;
		var editinfo = null;
		while (editinfo = all_sending_edits.pop()) {
			var field_data = this._findDataFromEditinfo(editinfo, data);
			
			var html = null;
			
			// We got something back
			if (field_data) {
				if (field_data.errors) {
					// TODO handle errors
					continue; // continue because we dont want to process back into rendered
				} else if (field_data.html) {
					html = field_data.html;
				}
			}
			
			// We dont have HTML, we'll have to guess what the rendered value is
			if (!html) {
				var value_arr = $(':input', editinfo.form_elements).serializeArray();
				var value_bits = [];
				value_arr.each(function (v) {
					value_bits.push(v.value);
				});

				html = value_bits.join(', ');
			}
			
			// Remove old rendered value
			editinfo.rendered_els.remove();
			editinfo.rendered_els = $('<div/>').html(html);
			this.closeEditinfo(editinfo);
		}
	},
	
	_findDataFromEditinfo: function(editinfo, data) {
		
		// A single 'field' can be made up of more than one actual form element
		// So the data we get back is often ID'd by the parent.
		// For example, date[mm] and date[yy] might be the real form elements,
		// but AJAX would return data for the field with the identifier simply 'date'.
		
		// Since each editable is for a single field, they must all share the same
		// prefix/group. So we can simply try to find the common prefix by removing
		// each sub-field one at a time.
		// 'date_mm': not found, so we cut down to just 'date': and its found
		
		var id = $(':input', editinfo.form_elements).eq(0).attr('id');
		var id_parts = id.split('_');
		
		do {
			var check_part = id_parts.join('_');
			if (data[check_part] != undefined) {
				return data[check_part];
			}
		} while (id_parts.pop());
		
		return false;
	},
	
	handleAjaxFailure: function(ajax_id) {
		// TODO retry? show error?
	},
	
	setEditinfoLoading: function (editinfo, is_multi) {
		// TODO loading el?
	},

	closeEditables: function() {
		var editinfo = null;
		while (editinfo = this.activeEdits.pop()) {
			this.closeEditinfo(editinfo);
		}
	},
	
	closeEditinfo: function(editinfo) {
		// 1. Move form back to old location
		// 2. Put rendered data bc
		
		var editable = editinfo.editable;
		var rendered_els = editinfo.rendered_els;
		var form_elements = editinfo.form_elements;
		var form_elements_container = editinfo.form_elements_container;
		
		form_elements.fadeOut('fast', function() {
			form_elements.removeClass('editable-fields-on').appendTo(form_elements_container);
			if (rendered_els.parent().get(0) != editable.get(0)) {
				rendered_els.hide().appendTo(editable).fadeIn('fast');
			}
		});	
	}
});