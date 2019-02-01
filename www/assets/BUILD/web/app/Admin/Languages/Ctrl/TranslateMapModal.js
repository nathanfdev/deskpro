define(['angular', 'Admin/Main/Ctrl/Base'], function(angular, Admin_Ctrl_Base) {
  class Admin_Languages_Ctrl_TranslateMapModal extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Languages_Ctrl_TranslateMapModal';
      this.CTRL_AS   = 'TranslateModal';
      this.DEPS      = ['$timeout', '$modalInstance', 'phrase_map', 'save_map', 'languages'];
    }

    init() {
      this.active_lang = null;
      this.active_trans = null;
      this.hasPendingPromise = false;

      this.$scope.dismiss = () => {
        return this.$modalInstance.dismiss('cancel');
      };

      this.$scope.save = () => {
        if (this.active_lang) {
          this.phrase_map[this.active_lang] = this.active_trans;

          this.save_map();
          return this.$modalInstance.close();
        }
      };

      this.$scope.$watch(() => {
        return this.active_lang;
      }
      , (newLangId, oldLangId) => {
        if (!oldLangId) { return; }

        if (!this.phrase_map[oldLangId]) { this.phrase_map[oldLangId] = {}; }
        this.phrase_map[oldLangId] = this.active_trans;

        if (this.phrase_map[newLangId]) {
          return this.active_trans = this.phrase_map[newLangId];
        } else {
          return this.active_trans = '';
        }
      });

      return this.$scope.$watch(() => {
        return this.active_trans;
      }
      , () => {
        if (!this.active_lang) { return; }
        return this.phrase_map[this.active_lang] = this.active_trans;
      });
    }

    initialLoad() {
      this.ctrl_is_loading = false;
      this.langs = this.languages;

      this.active_lang = this.langs[0].id;
      if (this.phrase_map[this.active_lang]) {
        return this.active_trans = this.phrase_map[this.active_lang];
      } else {
        return this.active_trans = null;
      }
    }
  }
  Admin_Languages_Ctrl_TranslateMapModal.initClass();

  return Admin_Languages_Ctrl_TranslateMapModal.EXPORT_CTRL();
});