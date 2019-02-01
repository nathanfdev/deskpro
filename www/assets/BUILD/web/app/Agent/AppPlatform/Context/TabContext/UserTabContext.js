define(['Agent/AppPlatform/Context/TabContext/TabContext'], TabContext => new Orb.Class({
  Extends: TabContext,

  getInjectables() {
    return [
        ['$person', this.getFragment().meta.api_data],
        ['$user', this.getFragment().meta.api_data],  // alias
    ];
  },

  getPersonData() {
    return this.getFragment().meta.api_data;
  }
}));
