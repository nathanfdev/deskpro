/** function jira() {
	$("#" + pageMeta.baseId + "_actions_menu_trigger").after("<li data-action='export-jira'><i class=\"icon-share\"></i> {{ phrase('agent.jira.export_jira') }}</li>");

	$("#" + pageMeta.baseId + "_tasks_wrap_tab").after('<li class="off" data-tab-for="#' + pageMeta.baseId + '_jira_wrap" id="' + pageMeta.baseId + '_jira_wrap_tab">JIRA (<span id="' + pageMeta.baseId + '_jira_count">0</span>)</li>');

	$("#" + pageMeta.baseId + "_tasks_wrap").after('<article id="' + pageMeta.baseId + '_jira_wrap" style="display: none;" class=""><div style="width: 100%; text-align: center;"><img src="/web/images/spinners/loading-big-circle.gif"/><br/>Please wait while we fetch the latest JIRA activities on this ticket.</div></article>');

	$("*[data-action='export-jira']").on("click", function(){
		var overlay = new DeskPRO.UI.Overlay({
			destroyOnClose: true,
			contentMethod: 'ajax',
			contentAjax: { url: BASE_URL + 'agent/jira/export/' + pageMeta.ticket_id },
			onOverlayOpened: function() {	
				selectProject("{{ app.getSetting('core.apps_jira.defaultProject') }}");
			}
		});

		overlay.open();
	});

	$("#" + pageMeta.baseId + "_jira_wrap_tab").click(function() {
		//$("#" + pageMeta.baseId + "_jira_wrap").html("<h1 style='text-align: center;'>Loading . . .Please wait</h1>");
		$("#" + pageMeta.baseId + "_jira_wrap").load(BASE_URL + 'agent/jira/issues/1', function(){
			$("#" + pageMeta.baseId + "_jira_wrap_tab").children('span').text($("#" + pageMeta.baseId + "_jira_wrap #issue-count").text());
		});

	});
}

function format(state) {
	var originalOption = state.element;

	return "<img class='icon' src='" + $(originalOption).data('icon') + "' />" + state.text;
} */

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
		
		this.fetchJiraComments();
		
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
		
		$("#" + self.baseId + "_export_jira_trigger").on("click", function(){			
			var overlay = new DeskPRO.UI.Overlay({
				
				destroyOnClose: true,
				
				contentMethod: 'ajax',
				
				contentAjax: { url: BASE_URL + 'agent/jira/export/' + self.ticketId },
				
				onOverlayOpened: function() {
					self.selectProject(self.defaultProject);
					
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
			
	fetchJiraComments: function() {
		var self = this;
		
		$.get(BASE_URL + 'agent/jira/issue/' + self.ticketId + '/fetchcomments', function(){
			setTimeout(function () {
				self.fetchJiraComments();
			}, 1000);
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
	}
});