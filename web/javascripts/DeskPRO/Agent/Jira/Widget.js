Orb.createNamespace('DeskPRO.Agent.Jira');

DeskPRO.Agent.Jira.Widget = new Orb.Class({
	initializeProperties: function() {
		this.ticketId = null;
	},
			
	initialize: function(options) {
		this.baseId			= options.baseId;
		this.ticketId		= options.ticketId;
		this.defaultProject	= options.defaultProject;
		
		this.hideJiraIssueTab();
		
		this.bindExportOverlay();
		
		this.fetchAssociatedJiraIssues();
		
		this.loadTimeout = setInterval(function () {
			$.get(BASE_URL + 'agent/jira/issue/' + options.ticketId + '/fetchcomments?dp_no_activity=1');
		}, 60000);
		
		this.bindJiraTab();
	},
			
	bindProjectDropdown: function() {
		$(document).off('change', '#jira-issue-project').on('change', '#jira-issue-project', this.lookupMeta);
	},
			
	bindExportFormSubmit: function() {
		var self = this;
		
		$('form.export-jira').on('submit', function(){
			self.exportFormSubmit();
			
			return false;
		});
	},
			
	bindExportOverlay: function() {
		var self = this;
		
		$("#" + self.baseId + "_export_jira_trigger").on("click", function() {

			var loadingOverlay = new DeskPRO.UI.Overlay({
				destroyOnClose: true,
				contentMethod: 'element',
				contentElement: $('<div><div class="alert-overlay mass-actions-overlay"><div class="overlay-title"><h4>Loading</h4></div><div class="overlay-content">Loading JIRA options... Please wait.</div></div></div>')
			});
			loadingOverlay.open();

			var overlay = new DeskPRO.UI.Overlay({
				destroyOnClose: true,
				contentMethod: 'ajax',
				contentAjax: { url: BASE_URL + 'agent/jira/export/' + self.ticketId },
				onOverlayOpened: function() {
					loadingOverlay.close();

					self.selectProject(self.defaultProject);
					$("#jira-issue-project").on('change', function() {
						self.lookupMeta();
					})
					self.bindExportFormSubmit();
				}
			});

			overlay.open();
		});
	},
	
	bindNewCommentOverlay: function() {
		var self = this;
		
		$("#jira-issue-table .icon-comment").on('click', function(){
			var issue_id = $(this).closest('tr').data('issue_id');
			
			self.openNewCommentOverlay(issue_id);
		});
	},
			
	bindUnlinkOverlay: function() {
		var self = this;
		
		$("#jira-issue-table .icon-remove").on('click', function(){
			var issue_id = $(this).closest('tr').data('issue_id');
			
			self.openUnlinkOverlay(issue_id);
		});
	},
			
	bindJiraTab: function() {
		var self = this;
		
		$("#" + self.baseId + "_jira_wrap_tab").click(function(){
			self.fetchAssociatedJiraIssues();
		});
	},
	
	selectProject: function(key) {
		$("#jira-issue-project").val(key);
		
		this.lookupMeta();
	},
	
	lookupMeta: function() {
		var self				= this;

		$('#jira-meta-fields').hide();
		$('#jira-meta-loading').show();

		var projectDropdown		= $('#jira-issue-project');
		
		var typeDropdown		= $('#jira-issue-type');
		
		var priorityDropdown	= $('#jira-issue-priority');
		
		var assigneeDropdown	= $('#jira-issue-assignee');
		
		typeDropdown.prop('disabled', true);
		
		try {
			typeDropdown.select2({
				enabled: false
			});
		} catch(err) {};
		
		priorityDropdown.prop('disabled', true);
		
		try {
			priorityDropdown.select2({
				enabled: false
			});
		} catch(err) {};
		
		assigneeDropdown.prop('disabled', true);
		
		try {
			assigneeDropdown.select2({
				enabled: false
			});
		} catch(err) {};
		
		var projectKey		= projectDropdown.val();
		
		$.getJSON( BASE_URL + "agent/jira/lookup",{
			"projectkey" : projectKey
		}).done(function(data) {
			if (data) {
				typeDropdown.html("");
				
				$.each(data.issuetypes, function(index, issuetype){
					typeDropdown.append("<option data-icon='" + issuetype.iconUrl + "' value='" + issuetype.id + "'>" + issuetype.name + "</option>");
				});
				
				priorityDropdown.html("");
				
				$.each(data.priorities, function(index, priority){
					priorityDropdown.append("<option data-icon='" + priority.iconUrl + "' value='" + priority.id + "'>" + priority.name + "</option>");
				});
				
				assigneeDropdown.html("");
				
				$.each(data.assignee, function(index, assignee){
					assigneeDropdown.append("<option data-icon='" + (assignee.avatarUrls.xsmall) + "' value='" + assignee.id + "'>" + assignee.displayName + "</option>");
				});
			} else {
				DeskPRO_Window._showAjaxError("Error Occured!");
			}
		})
		.fail(function() {
			DeskPRO_Window._showAjaxError("Error Occured!");
		})
		.always(function() {
			typeDropdown.prop('disabled', false);
			
			priorityDropdown.prop('disabled', false);
			
			assigneeDropdown.prop('disabled', false);
			
			$(".export-jira select.dpe_select").select2({
				formatResult: self.formatSelect,
				
				formatSelection: self.formatSelect,
				
				escapeMarkup: function(m) { return m; }
			});

			$('#jira-meta-fields').show();
			$('#jira-meta-loading').hide();
		});
	},
	
	formatSelect: function(state) {
		var originalOption = state.element;
		
		if (!$(originalOption).attr('data-icon')) {
			return state.text;
		}

		return "<img class='icon' src='" + $(originalOption).data('icon') + "' />" + state.text;
	},
			
	exportFormSubmit: function() {
		var self = this;
		
		var form = $("form.export-jira");
		
		form.find("div.is-not-loading").toggle();
		form.find("div.is-loading").toggle();
		
		var url		= form.data('target');
		
		var formData = form.serialize();
		
		$.post(url, formData, "json").done(function(data) {
			if (data && data.key) {
				DeskPRO_Window.showAlert("Issue " + data.key + " created successfully");
			} else {
				DeskPRO_Window.showError("There was a problem in exporting the ticket");
			}
			
			form.find("a.close").click();
			
			$("#" + self.baseId + "_jira_wrap_tab").click();
		}).fail(function(xhr) {
			var response = $.parseJSON(xhr.responseText);
			
			DeskPRO_Window._showAjaxError(response.message);
		}).always(function(){
			form.find("div.is-not-loading").toggle();
			form.find("div.is-loading").toggle();
		});
		
		return false;
	},
			
	fetchAssociatedJiraIssues: function() {
		var self = this;
		
		$("#" + pageMeta.baseId + "_jira_wrap").html('<h1 style="text-align: center;">Loading... Please wait</h1>');
		
		$("#" + pageMeta.baseId + "_jira_wrap").load( BASE_URL + "agent/jira/issue/" + this.ticketId, function( response, status, xhr ) {
			if ( xhr.status == "200" ) {
				$("#" + self.baseId + "_jira_wrap_tab").show();
				
				self.bindNewCommentOverlay();
				
				self.bindUnlinkOverlay();
				
				self.formatDates();
			}
			
			$(".jira_issue_count").text($("#jira-issue-table tbody tr").length);
		});
	},
	
	hideJiraIssueTab: function() {
		$("#" + this.baseId + "_jira_wrap_tab").hide();
	},
			
	openNewCommentOverlay: function(issue_id) {
		var self	= this;
		
		var overlay = new DeskPRO.UI.Overlay({
			destroyOnClose: true,
			contentMethod: 'ajax',
			contentAjax: { url: BASE_URL + 'agent/jira/' + issue_id + '/comment'},
			onOverlayOpened: function() {
				self.bindCommentFormSubmit();
			}
		});

		overlay.open();
	},
			
	openUnlinkOverlay: function(issue_id) {
		var self	= this;
		
		var ticketId = this.ticketId;
		
		var overlay = new DeskPRO.UI.Overlay({
			destroyOnClose: true,
			contentMethod: 'ajax',
			contentAjax: { url: BASE_URL + 'agent/jira/unlink/' + ticketId + '/' + issue_id},
			onOverlayOpened: function() {
				self.bindUnlinkFormSubmit();
			}
		});

		overlay.open();
	},
			
	formatDates: function() {
		$(".momentjs").each(function(index, td){
			var current = $(this);

			var day = moment(current.text());

			current.text(day.fromNow());
		});
	},
	
	bindCommentFormSubmit: function() {
		$(document).off('submit', 'form.post-comment').on('submit', 'form.post-comment', function(event){
			var form	= $(this).closest('form');
			
			form.find("div.is-not-loading").toggle();
			
			form.find("div.is-loading").toggle();

			var url		= form.data('target');

			var formData = form.serialize();

			var jQxhr = $.post(url, formData, "json").done(function(data) {
				DeskPRO_Window.showAlert("Comment posted successfully");

				form.find("a.close").click();
			}).fail(function(xhr) {
				var response = jQuery.parseJSON(xhr.responseText);

				DeskPRO_Window._showAjaxError(response.message);
			}).always(function(){
				form.find("div.is-not-loading").toggle();
				
				form.find("div.is-loading").toggle();
			});

			return false;
		});
	},
	
	bindUnlinkFormSubmit: function() {
		$(document).off('submit', 'form.unlink-issue').on('submit', 'form.unlink-issue', function(event){
			if (!confirm('Are you sure you want to unlink this issue from JIRA?')) {

				$(this).closest('form').find("a.close").click();

				return false;
			}

			var form	= $(this).closest('form');
			
			form.find("div.is-not-loading").toggle();
			
			form.find("div.is-loading").toggle();

			var url		= form.data('target');

			var formData = form.serialize();

			$.post(url, formData, "json").done(function(data) {
				var row = $("tr[data-issue_id=" + data.issue_id + "]");

				form.find("a.close").click();

				DeskPRO_Window.showAlert(data.message);

				row.fadeOut('slow', function(){
					$(this).remove();
				});
			}).fail(function(xhr) {
				var response = jQuery.parseJSON(xhr.responseText);

				DeskPRO_Window._showAjaxError(response.message);
			}).always(function(){
				form.find("div.is-not-loading").toggle();
				
				form.find("div.is-loading").toggle();
			});

			return false;
		});
	},

	destroy: function() {
		if (this.loadTimeout) {
			window.clearTimeout(this.loadTimeout);
			this.loadTimeout = null;
		}
	}
});