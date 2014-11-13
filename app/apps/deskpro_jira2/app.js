define([
	'deskpro_jira2/Ticket/ListCtrl',
	'deskpro_jira2/Service/Meta',
	'deskpro_jira2/Collection/Issues',
	], function(ListCtrl, Meta, IssuesCollection) {
	return {
		init: function() {

			var $inj = this.getPlatform().getNgInjector(),
				meta = $inj.instantiate(Meta),
				Issues = $inj.instantiate(IssuesCollection);

			this.registerAppWidget('ticket', 'Ticket/list.html', ListCtrl, {
				'$meta': meta,
				'Issues': Issues
			});
		}
	}
});