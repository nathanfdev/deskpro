define([
  'deskpro_jira/Ticket/ListCtrl',
  'deskpro_jira/Service/Meta',
  'deskpro_jira/Collection/Issues',
], function (ListCtrl, Meta, IssuesCollection) {
  return {
    init: function () {

      var $inj = this.getPlatform().getNgInjector(),
          meta = $inj.instantiate(Meta),
          Issues = $inj.instantiate(IssuesCollection);

      $('<link rel="stylesheet" type="text/css" />')
        .attr('href', this.getResourcePath('style.css'))
        .appendTo('body');

      this.registerAppWidget('ticket', 'Ticket/list.html', ListCtrl, {
        '$meta': meta,
        'Issues': Issues
      });
    }
  }
});