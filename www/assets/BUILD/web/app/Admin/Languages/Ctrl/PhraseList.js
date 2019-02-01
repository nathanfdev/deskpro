define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Languages_Ctrl_PhraseList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Languages_Ctrl_PhraseList';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      return this.id = this.$stateParams.id.replace(/^phrases\-/, '');
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        lang_info:     `/langs/${this.id}`,
        phrase_groups: '/langs/phrases-groups'
      }).then((result) => {
        if (!result.data.lang_info.language) {
          this.$state.go('setup.languages.install', { id: `install-${this.id}` });
          return;
        }

        this.pack = result.data.lang_info.pack;
        this.lang = result.data.lang_info.language;

        return this.phraseGroups = result.data.phrase_groups.phrase_groups;
      });
      return promise;
    }
  }
  Admin_Languages_Ctrl_PhraseList.initClass();

  return Admin_Languages_Ctrl_PhraseList.EXPORT_CTRL();
});
