/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['angular', 'Admin/Main/Ctrl/Base'], function(angular, Admin_Ctrl_Base) {
  class Admin_Languages_Ctrl_TranslateModal extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Languages_Ctrl_TranslateModal';
      this.CTRL_AS   = 'TranslateModal';
      this.DEPS      = ['$timeout', '$modalInstance', 'phraseId', 'editorOptions'];
    }

    init() {
      this.phrase_map = {};
      this.active_lang = null;
      this.active_trans = null;
      this.hasPendingPromise = false;
      this.options = this.editorOptions;

      this.$scope.dismiss = () => {
        return this.$modalInstance.dismiss('cancel');
      };

      this.$scope.save = () => {
        if (this.active_lang) {
          this.phrase_map[this.active_lang] = this.active_trans;
        }

        return this.savePhrases().then(() => {
          return this.$modalInstance.close();
        });
      };

      this.$scope.$watch(() => {
        return this.active_lang;
      }
      , (newLangId, oldLangId) => {
        if (!oldLangId) { return; }

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
      const p = this.Api.sendDataGet({
        langs: '/langs',
        lang_phrases: `/langs/phrases/${this.phraseId}`
      }).success( data => {
        this.ctrl_is_loading = false;
        if (this.options.exclude_own || this.options.exclude_default) {
          this.langs = [];
          for (let l of Array.from(data.langs.languages)) {
            if (this.options.exclude_own && (DP_PERSON_LANG_ID === l.id)) {
              continue;
            }
            if (this.options.exclude_default && (l.id === data.langs.default_lang_id)) {
              continue;
            }

            this.langs.push(l);
          }
        } else {
          this.langs = data.langs.languages;
        }

        let first = null;
        for (let phrase of Array.from(data.lang_phrases.lang_phrases)) {
          if (!first) { first = phrase; }
          const lang_id = phrase.language.id;
          this.phrase_map[lang_id] = phrase.phrase;
        }

        if (first) {
          this.active_lang  = first.language.id;
          return this.active_trans = first.phrase;
        } else {
          this.active_lang = this.langs[0].id;
          return this.active_trans = null;
        }
      });

      return p;
    }

    savePhrases() {
      let p, ret;
      const phrase_map = angular.copy(this.phrase_map);
      const phrase_id = this.phraseId;

      const api = this.Api;
      const saveInfo = {
        phrase_id,
        phrase_map,
        saver(phrase_id, phrase_map) {
          const postData = {'lang_phrases': []};

          for (let k of Object.keys(phrase_map || {})) {
            const v = phrase_map[k];
            postData.lang_phrases.push({
              phrase: v || '',
              language_id: k
            });
          }

          return api.sendPostJson(`/langs/phrases/${phrase_id}`, postData);
        }
      };

      saveInfo.save = () => saveInfo.saver(saveInfo.phrase_id, saveInfo.phrase_map);

      if (this.editorOptions.saveHandler) {
        ret = this.editorOptions.saveHandler(saveInfo.phrase_id, saveInfo.phrase_map, saveInfo.saver);
      } else {
        ret = saveInfo.save();
      }

      if (ret.then) {
        p = ret;
        this.$scope.is_loading = true;
        ret.then(() => { return this.$scope.is_loading = false; });
      } else {
        const defer = this.$q.defer();
        defer.resolve();
        p = defer.promise;
      }

      return p;
    }
  }
  Admin_Languages_Ctrl_TranslateModal.initClass();

  return Admin_Languages_Ctrl_TranslateModal.EXPORT_CTRL();
});