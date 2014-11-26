define(['Agent/AppPlatform/Context/TabContext/TabContext'], function(TabContext) {
  return new Orb.Class({
    Extends: TabContext,

    getInjectables: function() {
      return [
        ['$ticket', this.getFragment().meta.api_data],
        ['$person', this.getFragment().meta.api_data.person]
      ]
    },

    getTicketData: function() {
      return this.getFragment().meta.api_data;
    },

    getPersonData: function() {
      return this.getFragment().meta.api_data.person;
    }
  });
});