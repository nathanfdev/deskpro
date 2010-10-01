Orb.createNamespace('DeskPRO.Form');

/**
 * InlineEdit allows you to attach inline editing capabilities to any elements
 * within your page.
 *
 * OVERVIEW
 * ========
 * This class is more involved than many of the existing "inline editable" Javascript
 * plugins available. Since DeskPRO has many types of input fields with special handling
 * etc, we don't want to rewrite templates and other logic in Javascript.
 *
 * Instead, this class will either use pre-rendered form elements, or will fetch form HTML
 * via AJAX. We never actually render HTML ourselves here in JS. This whole module only cares
 * for triggers that replace a rendered value with a form, and then submitting it via AJAX.
 *
 * This class uses custom HTML attributes which won't validate, but will still work
 * in all browsers.
 *
 * HOW IT WORKS
 * ============
 * Each editable data is wrapped by a CSS class 'editable'. When the editable interface
 * is enabled, the innerHTML will be cleared and replaced with form input. When the data
 * is updated, the innerHTML will be cleared again and replaced with the new rendered data.
 *
 * An editable item has a custom attribute:
 * data-editable-for: An elemenet ID that has the field(s) HTML we want to edit. The
 * inner elements will be moved from this element into the 'editable' element.
 *
 * Web Page HTML
 * =============
 * You have two sections. The first section is the currently rendered value, and the second
 * section is the rendered form fields for editing the value:
 * 
 * <code>
 * 	Name:
 * 	<div class="editable" data-editable-for="edit_name">Christopher</div>
 * 
 * 	<!-- A simple hidden container to contain HTML for fields -->
 * 	<div style="display:none">
 * 		<div id="edit_name"><input type="input" name="person[fullname]" value="Christopher" /></div>
 * 	</div>
 * </code>
 * 
 * Script Handler
 * ==============
 * 
 * When AJAX is submitted, a 'data' array is supplied with the array key being a unique ID, and the array
 * value being an array of data fields. For example, when submitting the above snippet, the request might look like:
 * 
 *     save?data[editable_1235][person][fullname]=Chris
 * 
 * The AJAX must return JSON encoded data with an array of 'fields':
 * 
 * <code>
 * 	{
 * 		fields:
 * 		[
 * 			{
 * 				id: 'id_from_data_array',
 * 				status: 'success',
 * 				renderedValue: 'HTML display of new value',
 * 				renderedForm: 'New input HTML for form fields'
 * 			},
 * 			{
 * 				id: 'id_from_data_array',
 * 				status: 'error',
 * 				errorMessages: ['Message 1', 'Message 2']
 * 			}
 * 		]
 * 	}
 * </code>
 * 
 *
 *
 * @option {Element} baseElement                  The base element to search for the selectors in
 * @option {String}  editableClass                The classname we'll search for for editables
 * @option {Boolean} autoSave                     Automatically 'close' the editable after clicking off, and save.
 *                                                If false, then saving is done some other way (such as a button).
 * @option {String} errorContainerSelector        An error container within a form wrapper element that will be displayed if
 *                                                server returns an error status.
 * @option {String} errorListSelector             A list within the error container that will have error messages appended to it
 * @option {Object} ajax                          A hash of AJAX info. The most important being that you suppy the 'url' item.
 * @option {Object} ajaxData                      Data to send in the AJAX call. Do not use the key 'data'.
 */
DeskPRO.Form.InlineEdit = new Class({
	Implements: Options,
	
	/**
	 * Hash of options
	 * @var {Object}
	 */
	options: {
		baseElement: document,
		editableClass: 'editable',
		autoSave: false,
		errorContainerSelector: '.errors',
		errorListSelector: 'ul',
		ajax: {
			timeout: 20000,
			type: 'POST',
			url: ''
		},
		ajaxData: {},
	},
	
	/**
	 * An array of open editables. If autoSave is enabled, this is always just one.
	 * @var {Array}
	 */
	pending_edits: [],
	
	/**
	 * A Hash of edits that have been sent out, keyed by ID. This is used in the ajax
	 * callback to properly replace new values back into the dom.
	 * @var {Hash}
	 */
	sending_edits: null,
	
	
	
	initialize: function (options) {
		this.setOptions(options);
		
		if (!this.options['formContainer']) {
			this.options['formContainer'] = $('<div style="display:none"></div>').appendTo(document);
		}
		
		this.sending_edits = new Hash();
		
		var sel = '.' + this.options['editableClass'];
		var self = this;
		$(sel, this.options['baseElement']).each(function() { self.initEditable(this); });
	},
	
	
	
	/**
	 * Initialize an editable by attaching new triggers
	 *
	 * @param {HTMLElement} el
	 */
	initEditable: function(el) {
		var self = this;
		
		var jel = $(el);
		jel.dblclick(function() { self.startEditable(this); });
		
		if (!jel.attr('id')) {
			jel.attr('id', Orb.getUniqueId('editable_'));
		}
	},
	
	
	
	/**
	 * Start editing an element. This removes the rendered value and replaces
	 * it with the form fields.
	 *
	 * @param {HTMLElement} el
	 */
	startEditable: function(el) {
		var form_wrap = this.getEditableFields(el);
		var form_fields = form_wrap.children();
		
		// We detatch it from the DOM now
		form_fields.detatch();
		
		var state_info = { 'el': el, 'html': $(el).html(), 'form_wrap': form_wrap };
		
		// And add it into the editable wrapper
		$(el).html('').appendTo(form_fields);
		
		this.pending_edits.push(state_info);
	},
	
	
	/**
	 * Submit all pending editables via ajax.
	 */
	submitEditables: function() {
		
		var req_id = Orb.uuid();
		var data_parts = $extend({
			{ name: 'editable_request_id', value: req_id }
		}, this.options['ajaxData']);
		
		while (var edit = this.pending_edits.shift()) {
			
			var el_id = $(edit.el).attr('id');
			var form_data = $(edit.el).serializeArray();
			data_parts[el_id] = form_data;
			
			// TODO put a loading indicator now or dim out fields
			// or something
			
			edit.req_id = req_id;
			this.sending_edits.set(el_id, edit);
		}
		
		var ajax_options = $extend({
			context: this,
			success: this.submitDone,
			dataType: 'json',
			//TODO
			//error: this.submitError,
			data: data_parts
		}, this.options['ajax']);
		
		$.ajax(ajax_options);
	},
	
	
	
	/**
	 * Callback function called after AJAX data has been saved to the server
	 */
	submitDone: function(data, textStatus, XMLHttpRequest) {
		
		while (info = data.fields.pop()) {
			var el = $('#' + info.id);
			
			var errorContainer = $(this.options['errorContainerSelector'], el);
			var errorList = $(this.options['errorListSelector'], errorContainer);
			errorContainer.hide()
			errorList.hide();
			errorList.html('');
			
			// If theres an error with the data, then show the error div
			// and any messages provided
			if (info.status == 'error') {
				if (info.errorMessages) {
					while (errorMessage = info.errorMessages.shift()) {
						errorList.append('<li>' + errorMessage + '</li>');
					}
					errorList.show();
					errorContainer.show();
				}

			// On success, we have to 1) replace the rendered value,
			// and 2) replace the rendered form value (incase we edit again)
			} else {
				var sending_info = this.sending_edits.get(info.id);
				
				el.html(info.renderedValue);
				sendinf_info.form_wrap.html(info.renderedForm);
				this.sending_edits.erase(info.id);
			}
		}
	},
	
	
	
	/**
	 * Cancel all editables by returning things to their natural
	 * state.
	 */
	cancelEditables: function(el) {
		this.pending_edits.each(function (item) {
			// Move the input elements back to the hidden form container
			var form_fields = $(item.el).children();
			form_fields.detatch();
			item.form_wrap.append(form_fields);
			
			// Put the original HTML back
			$(item.el).html(item.html);
		});
	},
	
	
	
	/**
	 * Get the wrapper that contains the fields for an editable.
	 *
	 * @param {HTMLElement} el
	 */
	getEditableFields: function(el) {
		var form_wrap_id = $(el).attr('data-editable-for');
		var form_wrap = $('#' + form_wrap_id);
		
		return form_wrap;
	}
});






