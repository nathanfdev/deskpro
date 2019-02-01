define(['Agent/AppPlatform/Context/TabContext/TabContext'], TabContext => new Orb.Class({
  Extends: TabContext,

  getInjectables() {
    return [
        ['$ticket', this.getFragment().meta.api_data],
        ['$person', this.getFragment().meta.api_data.person]
    ];
  },

  getTicketData() {
    return this.getFragment().meta.api_data;
  },

  getPersonData() {
    return this.getFragment().meta.api_data.person;
  }
}));
