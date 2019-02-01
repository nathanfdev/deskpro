define(['Agent/AppPlatform/Context/TabContext/TabContext'], TabContext => new Orb.Class({
  Extends: TabContext,

  getInjectables() {
    return [
        ['$org', this.getFragment().meta.api_data]
    ];
  },

  getOrgData() {
    return this.getFragment().meta.api_data;
  }
}));
